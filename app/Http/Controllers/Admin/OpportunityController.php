<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use App\Models\OpportunityNote;
use App\Models\User;
use App\Support\Admin\LeadConverter;
use App\Support\Opportunities\Collector;
use App\Support\Opportunities\Countries;
use App\Support\Opportunities\ManualEntry;
use App\Support\Opportunities\OpportunityLinks;
use App\Support\Opportunities\PartnerPitch;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * „Възможности“: обществени поръчки (TED, Contracts Finder, Find a Tender), агенции партньори и ръчно добавени възможности
 * (платформи за свободна практика, грантове). Екранът само показва, оценява и следи работата. НИЩО не се подава и не се праща автоматично.
 */
class OpportunityController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'open');
        $kind = array_key_exists($request->query('kind'), Opportunity::KINDS) ? $request->query('kind') : null;
        $source = array_key_exists($request->query('source'), Opportunity::SOURCES) ? $request->query('source') : null;
        $country = preg_match('/^[A-Za-z]{2}$/', (string) $request->query('country')) ? strtoupper($request->query('country')) : null;
        $mine = $request->query('who') === 'me';
        $sort = in_array($request->query('sort'), ['score', 'deadline', 'new'], true) ? $request->query('sort') : 'score';
        $q = trim((string) $request->query('q'));
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';

        $base = Opportunity::query()
            ->when($kind, fn ($query) => $query->where('kind', $kind))
            ->when($source, fn ($query) => $query->where('source', $source))
            ->when($country, fn ($query) => $query->where('country', $country))
            ->when($mine, fn ($query) => $query->where('assigned_to', $request->user()->id))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('buyer', 'like', $like)));

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $statusFilter = $status === 'open' ? Opportunity::OPEN_STATUSES : (array_key_exists($status, Opportunity::STATUSES) ? [$status] : null);

        $list = (clone $base)->with(['assignee:id,name'])
            ->when($statusFilter, fn ($query) => $query->whereIn('status', $statusFilter))
            ->when($sort === 'score', fn ($query) => $query->orderByDesc('score')->orderByRaw('deadline_at is null')->orderBy('deadline_at'))
            ->when($sort === 'deadline', fn ($query) => $query->orderByRaw('deadline_at is null')->orderBy('deadline_at')->orderByDesc('score'))
            ->when($sort === 'new', fn ($query) => $query->orderByDesc('id'))
            ->paginate(25)->withQueryString();

        return view('admin.opportunities.index', [
            'opportunities' => $list,
            'counts' => $counts,
            'openCount' => collect(Opportunity::OPEN_STATUSES)->sum(fn ($s) => $counts[$s] ?? 0),
            'mineCount' => Opportunity::where('assigned_to', $request->user()->id)->whereIn('status', Opportunity::OPEN_STATUSES)->count(),
            'countries' => Opportunity::whereNotNull('country')->distinct()->orderBy('country')->pluck('country')->mapWithKeys(fn ($c) => [$c => Countries::name($c)]),
            'team' => User::orderBy('name')->get(['id', 'name']),
            'state' => Collector::state(),
            'feeds' => Collector::feeds(),
            'enabled' => Collector::enabled(),
            'searches' => OpportunityLinks::get('searches'),
            'grants' => OpportunityLinks::get('grants'),
            'filters' => compact('status', 'kind', 'source', 'country', 'mine', 'sort', 'q'),
        ]);
    }

    public function show(Opportunity $opportunity)
    {
        $opportunity->load(['notes.user', 'assignee', 'project', 'prospect']);

        return view('admin.opportunities.show', [
            'o' => $opportunity,
            'team' => User::orderBy('name')->get(['id', 'name']),
            'cpvNames' => config('creatium.opportunities.cpv'),
            'pitch' => $opportunity->kind === 'partner' && $opportunity->prospect
                ? ['subject' => $opportunity->prospect->draft_subject ?: PartnerPitch::subject($opportunity->prospect), 'body' => $opportunity->prospect->draft_observation ?: PartnerPitch::body($opportunity->prospect)]
                : null,
        ]);
    }

    /** Добавяне на ръка: платформа за свободна практика, грант или нещо друго. Един и същ адрес не се добавя два пъти. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'kind' => ['required', Rule::in(array_keys(Opportunity::KINDS))],
            'title' => 'required|string|max:300',
            'url' => ['nullable', 'url:http,https', 'max:1000'],
            'origin' => 'nullable|string|max:80',
            'country' => ['nullable', 'string', 'size:2'],
            'budget' => 'nullable|numeric|min:0|max:100000000',
            'currency' => ['nullable', Rule::in(['EUR', 'GBP', 'USD', 'BGN'])],
            'deadline' => 'nullable|date|after_or_equal:today',
            'note' => 'nullable|string|max:2000',
        ]);

        $result = ManualEntry::add($data, $request->user());
        if (! $result['created']) {
            return back()->withInput()->withErrors(['url' => 'Този адрес вече е в списъка: ' . $result['opportunity']->title . '.'])->with('duplicate_id', $result['opportunity']->id);
        }

        return redirect()->route('admin.opportunities.show', $result['opportunity'])->with('success', 'Добавена. Оценка ' . $result['opportunity']->score . '/100.');
    }

    public function update(Request $request, Opportunity $opportunity)
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(array_keys(Opportunity::STATUSES))],
            'skip_reason' => 'nullable|string|max:200',
            'note' => 'sometimes|nullable|string|max:2000',
            'next_action_at' => 'sometimes|nullable|date',
            'assigned_to' => ['sometimes', 'nullable', Rule::exists('users', 'id')],
        ]);

        if (array_key_exists('next_action_at', $data)) {
            $opportunity->next_action_at = filled($data['next_action_at']) ? Carbon::parse($data['next_action_at'], 'Europe/Sofia')->setTime(9, 0)->utc() : null;
        }
        foreach (['note', 'assigned_to'] as $field) {
            if (array_key_exists($field, $data)) {
                $opportunity->{$field} = $data[$field] ?: null;
            }
        }
        $opportunity->save();

        if (isset($data['status']) && $data['status'] !== $opportunity->status) {
            $opportunity->moveTo($data['status'], $request->user()->id, $data['status'] === 'skipped' ? ($data['skip_reason'] ?? null) : null);
        }

        return back()->with('success', 'Записано.');
    }

    public function interesting(Request $request, Opportunity $opportunity)
    {
        if ($opportunity->status === 'new') {
            $opportunity->moveTo('interesting', $request->user()->id);
        }

        return back()->with('success', 'Отбелязана като интересна.');
    }

    public function skip(Request $request, Opportunity $opportunity)
    {
        $reason = $request->validate(['skip_reason' => 'nullable|string|max:200'])['skip_reason'] ?? null;
        $opportunity->moveTo('skipped', $request->user()->id, $reason);

        return back()->with('success', 'Пропусната.');
    }

    /** „Поемам я“: възлага се на мен, а нова възможност става интересна. */
    public function take(Request $request, Opportunity $opportunity)
    {
        $opportunity->assigned_to = $request->user()->id;
        $opportunity->save();
        if ($opportunity->status === 'new') {
            $opportunity->moveTo('interesting', $request->user()->id);
        }

        return back()->with('success', 'Поета от вас.');
    }

    public function note(Request $request, Opportunity $opportunity)
    {
        $data = $request->validate(['body' => 'required|string|max:2000']);
        OpportunityNote::create(['opportunity_id' => $opportunity->id, 'user_id' => $request->user()->id, 'kind' => 'note', 'body' => $data['body']]);

        return back()->with('success', 'Бележката е записана.');
    }

    public function toProject(Request $request, Opportunity $opportunity)
    {
        $project = LeadConverter::fromOpportunity($opportunity, $request->user());

        return redirect()->route('admin.projects.show', $project)->with('success', 'Проектът е създаден. Нищо не е подадено никъде.');
    }

    /** Опреснява източниците веднага. Може да отнеме около минута. */
    public function refresh(Collector $collector)
    {
        @set_time_limit(180);
        $results = $collector->refreshNow();
        $parts = [];
        $failed = false;
        foreach ($results as $key => $r) {
            if ($r === null) {
                $parts[] = $key . ': вече се опреснява';
            } elseif ($r['error']) {
                $failed = true;
                $parts[] = $key . ': грешка ' . $r['error'];
            } else {
                $parts[] = $key . ': върнати ' . $r['returned'] . ', нови ' . $r['new'];
            }
        }

        return back()->with($failed ? 'error' : 'success', implode('; ', $parts) . '.');
    }

    /** Редактира подготвените търсения и връзките за грантове. Формат „Име | адрес“, по един на ред. */
    public function links(Request $request)
    {
        $data = $request->validate(['searches' => 'nullable|string|max:8000', 'grants' => 'nullable|string|max:4000']);
        OpportunityLinks::save('searches', OpportunityLinks::parse((string) ($data['searches'] ?? '')));
        OpportunityLinks::save('grants', OpportunityLinks::parse((string) ($data['grants'] ?? '')));

        return back()->with('success', 'Връзките са записани.');
    }
}
