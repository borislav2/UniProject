<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientPayment;
use App\Models\Project;
use App\Support\Admin\SiteMonitor;
use Illuminate\Http\Request;

/** Клиентите на месечен абонамент: колко изкарваме, кой е платил и кой закъснява, работят ли сайтовете им. */
class ClientController extends Controller
{
    public function index()
    {
        $clients = Project::subscribers()->with('payments')->orderBy('name')->get();
        $period = now('Europe/Sofia')->format('Y-m');

        return view('admin.clients.index', [
            'clients' => $clients,
            'mrr' => $clients->sum('monthly_fee'),
            'paidThisMonth' => ClientPayment::where('period', $period)->whereIn('project_id', $clients->pluck('id'))->sum('amount'),
            'late' => $clients->filter(fn ($client) => $client->paymentState()['state'] === 'late')->count(),
            'sitesWithProblems' => $clients->filter(fn ($client) => SiteMonitor::problems($client))->count(),
        ]);
    }

    /** „Платено“ за месеца, който е на ред (или посочения), с месечната такса. */
    public function paid(Request $request, Project $project)
    {
        $data = $request->validate([
            'period' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'amount' => 'nullable|numeric|min:0|max:100000',
        ]);
        $period = $data['period'] ?? $project->paymentState()['period'];
        $amount = $data['amount'] ?? $project->monthly_fee;

        ClientPayment::updateOrCreate(['project_id' => $project->id, 'period' => $period], [
            'amount' => $amount, 'paid_on' => now('Europe/Sofia')->toDateString(), 'user_id' => $request->user()->id,
        ]);
        $month = \Illuminate\Support\Carbon::createFromFormat('Y-m', $period)->locale('bg')->isoFormat('MMMM YYYY');
        $project->notes()->create(['user_id' => $request->user()->id, 'kind' => 'note', 'body' => "Платено за {$month}: " . number_format((float) $amount, 2, ',', ' ') . ' €']);

        return back()->with('success', "{$project->name}: отбелязано плащане за {$month}.");
    }

    /** Проверка на сайта на клиента сега (бутон „Провери“). */
    public function check(Project $project, SiteMonitor $monitor)
    {
        abort_unless($project->website_url, 404);
        $monitor->check($project);
        $problems = SiteMonitor::problems($project->refresh());

        return back()->with($problems ? 'error' : 'success', $project->name . ': ' . ($problems ? implode('; ', $problems) : 'сайтът работи.'));
    }
}
