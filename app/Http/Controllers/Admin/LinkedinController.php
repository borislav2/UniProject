<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LinkedinProspect;
use App\Models\User;
use App\Support\Admin\LeadConverter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * „Потенциални клиенти“ → „LinkedIn“: хората и фирмите, които екипът намира на ръка в LinkedIn.
 * Админът не се свързва с LinkedIn (забранено е да се събира и пише автоматично): само помни кой е кой, докъде е стигнало и кога да напомним.
 */
class LinkedinController extends Controller
{
    private const FIELDS = ['name', 'title', 'company', 'city', 'industry', 'website', 'email', 'phone'];

    public function index(Request $request)
    {
        $status = $request->query('status');
        $mine = $request->query('who') === 'me';
        $due = $request->boolean('due');
        $q = trim((string) $request->query('q'));
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';

        return view('admin.linkedin.index', [
            'prospects' => LinkedinProspect::with(['assignee:id,name', 'project:id'])
                ->when($status && array_key_exists($status, LinkedinProspect::STATUSES), fn ($query) => $query->where('status', $status))
                ->when($mine, fn ($query) => $query->where('assigned_to', $request->user()->id))
                ->when($due, fn ($query) => $query->due())
                ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('company', 'like', $like)->orWhere('city', 'like', $like)->orWhere('profile_url', 'like', $like)))
                ->orderByRaw('case when next_action_at is not null and date(next_action_at) <= ? then 0 else 1 end', [now('Europe/Sofia')->toDateString()])
                ->orderByDesc('id')
                ->paginate(30)->withQueryString(),
            'counts' => LinkedinProspect::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'dueCount' => LinkedinProspect::due()->count(),
            'mineCount' => LinkedinProspect::where('assigned_to', $request->user()->id)->count(),
            'today' => LinkedinProspect::todayStats(),
            'team' => User::orderBy('name')->get(['id', 'name']),
            'filters' => ['status' => $status, 'mine' => $mine, 'due' => $due, 'q' => $q],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'profile_url' => 'required|string|max:500',
            'name' => 'nullable|string|max:150',
            'title' => 'nullable|string|max:150',
            'company' => 'nullable|string|max:150',
            'city' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:60',
            'note' => 'nullable|string|max:2000',
        ]);

        $url = LinkedinProspect::normalizeUrl($data['profile_url']);
        if (! $url) {
            return back()->withInput()->withErrors(['profile_url' => 'Нужен е адрес на профил (linkedin.com/in/…) или на фирмена страница (linkedin.com/company/…).']);
        }
        if ($existing = LinkedinProspect::where('profile_url', $url['url'])->first()) {
            return back()->withInput()->withErrors(['profile_url' => 'Този профил вече е в списъка: ' . $existing->name . '.']);
        }

        LinkedinProspect::create(array_filter(collect($data)->except('profile_url')->all(), fn ($v) => $v !== null) + [
            'profile_url' => $url['url'],
            'kind' => $url['kind'],
            'name' => filled($data['name'] ?? null) ? $data['name'] : LinkedinProspect::nameFromSlug($url['slug']),
            'added_by' => $request->user()->id,
            'assigned_to' => $request->user()->id,
        ]);

        return redirect()->route('admin.linkedin.index')->with('success', 'Добавено.');
    }

    /** Много адреси наведнъж: по един на ред, по желание „адрес | Име | Фирма“. Повторенията и грешните адреси се пропускат. */
    public function bulk(Request $request)
    {
        $request->validate(['lines' => 'required|string|max:60000']);

        $added = 0;
        $duplicates = [];
        $invalid = [];
        foreach (preg_split('/\r\n|\r|\n/', $request->input('lines')) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            [$raw, $name, $company] = array_pad(array_map('trim', explode('|', $line, 3)), 3, null);
            $url = LinkedinProspect::normalizeUrl($raw);
            if (! $url) {
                $invalid[] = mb_substr($raw, 0, 60);

                continue;
            }
            if (LinkedinProspect::where('profile_url', $url['url'])->exists()) {
                $duplicates[] = $url['slug'];

                continue;
            }
            LinkedinProspect::create([
                'profile_url' => $url['url'],
                'kind' => $url['kind'],
                'name' => filled($name) ? mb_substr($name, 0, 150) : LinkedinProspect::nameFromSlug($url['slug']),
                'company' => filled($company) ? mb_substr($company, 0, 150) : null,
                'added_by' => $request->user()->id,
                'assigned_to' => $request->user()->id,
            ]);
            $added++;
        }

        $message = "Добавени: {$added}.";
        if ($duplicates) {
            $message .= ' Вече в списъка (' . count($duplicates) . '): ' . implode(', ', array_slice($duplicates, 0, 5)) . (count($duplicates) > 5 ? '…' : '') . '.';
        }
        if ($invalid) {
            $message .= ' Не са адреси от LinkedIn (' . count($invalid) . '): ' . implode(', ', array_slice($invalid, 0, 3)) . (count($invalid) > 3 ? '…' : '') . '.';
        }

        return redirect()->route('admin.linkedin.index')->with($added ? 'success' : 'send_error', $message);
    }

    public function update(Request $request, LinkedinProspect $prospect)
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(array_keys(LinkedinProspect::STATUSES))],
            'name' => 'sometimes|required|string|max:150',
            'title' => 'sometimes|nullable|string|max:150',
            'company' => 'sometimes|nullable|string|max:150',
            'city' => 'sometimes|nullable|string|max:100',
            'industry' => 'sometimes|nullable|string|max:100',
            'website' => ['sometimes', 'nullable', 'url:http,https', 'max:255'],
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:60',
            'note' => 'sometimes|nullable|string|max:2000',
            'next_action_at' => 'sometimes|nullable|date',
            'assigned_to' => ['sometimes', 'nullable', Rule::exists('users', 'id')],
        ]);

        $status = $data['status'] ?? null;
        unset($data['status']);
        $prospect->fill($data);
        if ($status && $status !== $prospect->status) {
            $prospect->moveTo($status, $request->user()->id);
        }
        $prospect->save();

        return back()->with('success', $prospect->name . ': ' . $prospect->statusLabel() . '.');
    }

    public function destroy(LinkedinProspect $prospect)
    {
        $prospect->delete();

        return back()->with('success', 'Изтрито от списъка.');
    }

    public function toLead(Request $request, LinkedinProspect $prospect)
    {
        $lead = LeadConverter::fromLinkedin($prospect, $request->user());

        return redirect()->route('admin.projects.show', $lead)->with('success', 'Запитването е създадено. Запишете следващата стъпка.');
    }
}
