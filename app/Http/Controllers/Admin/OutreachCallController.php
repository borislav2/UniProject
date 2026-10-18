<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutreachCall;
use App\Support\Outreach\Outreach;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/** „Потенциални клиенти“ → „Без сайт“: бизнеси без сайт от Harvey, за обаждане по телефона. */
class OutreachCallController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'status' => array_key_exists($request->query('status'), OutreachCall::STATUSES) ? $request->query('status') : null,
            'city' => $request->query('city') ?: null,
            'industry' => $request->query('industry') ?: null,
            'phone' => $request->boolean('phone'),
        ];

        $path = config('creatium.outreach.harvey_no_website_csv');

        return view('admin.outreach.calls', [
            'calls' => OutreachCall::query()
                ->when($filters['status'], fn ($q, $v) => $q->where('status', $v))
                ->when($filters['city'], fn ($q, $v) => $q->where('city', $v))
                ->when($filters['industry'], fn ($q, $v) => $q->where('industry', $v))
                ->when($filters['phone'], fn ($q) => $q->whereNotNull('phone'))
                ->orderByRaw("status = 'new' desc")->orderByRaw('phone is null')->orderBy('city')->orderBy('name')
                ->paginate(50)->withQueryString(),
            'counts' => OutreachCall::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'cities' => OutreachCall::whereNotNull('city')->distinct()->orderBy('city')->pluck('city'),
            'industries' => OutreachCall::whereNotNull('industry')->distinct()->orderBy('industry')->pluck('industry'),
            'filters' => $filters,
            'serverCsv' => is_readable($path) ? Carbon::createFromTimestamp(filemtime($path)) : null,
        ]);
    }

    /** Внася последния списък без сайт, изнесен от Harvey на сървъра. */
    public function import()
    {
        $path = config('creatium.outreach.harvey_no_website_csv');
        if (! is_readable($path)) {
            return redirect()->route('admin.outreach.calls.index')->with('send_error', 'Няма списък без сайт от Harvey на сървъра (' . $path . '). Изнася се всеки ден заедно с имейлите.');
        }

        $result = Outreach::importCalls($path);

        return redirect()->route('admin.outreach.calls.index')->with('success', "От сървъра: {$result['added']} нови, {$result['updated']} обновени, {$result['skipped']} пропуснати, {$result['foreign']} извън България.");
    }

    public function update(Request $request, OutreachCall $call)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(OutreachCall::STATUSES))],
            'note' => 'nullable|string|max:1000',
        ]);

        $call->fill(['status' => $validated['status'], 'note' => $validated['note'] ?? null]);
        if ($call->isDirty('status') && $validated['status'] !== 'new') {
            $call->called_at = now();
        }
        $call->save();

        return back()->with('success', $call->name . ': ' . OutreachCall::STATUSES[$call->status] . '.');
    }
}
