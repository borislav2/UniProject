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
        $filters = $this->filters($request);
        $path = config('creatium.outreach.harvey_no_website_csv');
        $today = OutreachCall::today();

        return view('admin.outreach.calls', [
            'calls' => $this->filtered($filters)
                ->when($filters['status'], fn ($q, $v) => $q->where('status', $v))
                ->when($filters['due'], fn ($q) => $q->whereDate('callback_on', '<=', $today)->whereNotIn('status', OutreachCall::FINAL_STATUSES))
                ->orderByRaw('case when callback_on is not null and date(callback_on) <= ? then 0 else 1 end', [$today])
                ->orderByRaw("case when status = 'new' then 0 else 1 end")
                ->orderByRaw('case when phone is null then 1 else 0 end')
                ->orderByDesc('is_mobile')->orderBy('city')->orderBy('name')
                ->paginate(50)->withQueryString(),
            'counts' => OutreachCall::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'dueCount' => OutreachCall::whereDate('callback_on', '<=', $today)->whereNotIn('status', OutreachCall::FINAL_STATUSES)->count(),
            'queueCount' => $this->filtered($filters)->queue()->count(),
            'today' => $this->todayStats(),
            'cities' => OutreachCall::whereNotNull('city')->distinct()->orderBy('city')->pluck('city'),
            'industries' => OutreachCall::whereNotNull('industry')->distinct()->orderBy('industry')->pluck('industry'),
            'filters' => $filters,
            'serverCsv' => is_readable($path) ? Carbon::createFromTimestamp(filemtime($path)) : null,
        ]);
    }

    /**
     * „Следващо обаждане“: един бизнес от опашката (OutreachCall::queue) със скрипта за разговора.
     * Филтрите (град, вид, мобилен) се пазят в адреса; „Пропусни“ го скрива до края на сесията.
     */
    public function next(Request $request)
    {
        $filters = $this->filters($request);
        $skipped = $request->session()->get('outreach_calls_skipped', []);
        $query = $this->filtered($filters)->queue()->whereNotIn('id', $skipped);

        return view('admin.outreach.call-next', [
            'call' => (clone $query)->first(),
            'left' => $query->count(),
            'today' => $this->todayStats(),
            'filters' => $filters,
            'query' => $this->filterQuery($filters),
        ]);
    }

    public function skip(Request $request, OutreachCall $call)
    {
        $request->session()->push('outreach_calls_skipped', $call->id);

        return redirect()->route('admin.outreach.calls.next', $this->filterQuery($this->filters($request)));
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
            'callback_on' => 'nullable|date',
        ]);

        // Дата днес или по-рано значи, че обаждането е направено: махаме я, за да не остане в опашката.
        $callback = filled($validated['callback_on'] ?? null) ? Carbon::parse($validated['callback_on'])->toDateString() : null;
        $call->fill([
            'status' => $validated['status'],
            'note' => $validated['note'] ?? null,
            'callback_on' => $callback && $callback > OutreachCall::today() ? $callback : null,
        ]);
        // Едно обаждане на ден се брои веднъж: смяна на статуса или първото запазване за деня (без „За обаждане“).
        $calledToday = $call->called_at && $call->called_at->timezone('Europe/Sofia')->toDateString() === OutreachCall::today();
        if ($validated['status'] !== 'new' && ($call->isDirty('status') || ! $calledToday)) {
            $call->called_at = now();
            $call->attempts = min(255, $call->attempts + 1);
        }
        $call->save();

        $message = $call->name . ': ' . OutreachCall::STATUSES[$call->status] . ($call->callback_on ? ', ново обаждане на ' . $call->callback_on->format('d.m.Y') : '') . '.';
        if ($request->boolean('next')) {
            return redirect()->route('admin.outreach.calls.next', $this->filterQuery($this->filters($request)))->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /** @return array{status: ?string, city: ?string, industry: ?string, phone: bool, mobile: ?string, due: bool} */
    private function filters(Request $request): array
    {
        return [
            'status' => array_key_exists((string) $request->input('status'), OutreachCall::STATUSES) ? $request->input('status') : null,
            'city' => $request->input('city') ?: null,
            'industry' => $request->input('industry') ?: null,
            'phone' => $request->boolean('phone'),
            'mobile' => in_array($request->input('mobile'), ['mobile', 'landline'], true) ? $request->input('mobile') : null,
            'due' => $request->boolean('due'),
        ];
    }

    /** Град, вид и вид телефон: общите филтри за списъка и за „Следващо обаждане“. */
    private function filtered(array $filters)
    {
        return OutreachCall::query()
            ->when($filters['city'], fn ($q, $v) => $q->where('city', $v))
            ->when($filters['industry'], fn ($q, $v) => $q->where('industry', $v))
            ->when($filters['phone'], fn ($q) => $q->whereNotNull('phone'))
            ->when($filters['mobile'] === 'mobile', fn ($q) => $q->where('is_mobile', true))
            ->when($filters['mobile'] === 'landline', fn ($q) => $q->whereNotNull('phone')->where('is_mobile', false));
    }

    private function filterQuery(array $filters): array
    {
        return array_filter(['city' => $filters['city'], 'industry' => $filters['industry'], 'mobile' => $filters['mobile']]);
    }

    /** Броячът за деня: колко бизнеса са обадени днес (София) и с какъв резултат. */
    private function todayStats(): array
    {
        $byStatus = OutreachCall::where('called_at', '>=', now('Europe/Sofia')->startOfDay()->utc())
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'by_status' => $byStatus,
            'goal' => max(1, (int) config('creatium.outreach.daily_call_goal')),
        ];
    }
}
