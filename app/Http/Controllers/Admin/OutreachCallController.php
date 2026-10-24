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
        // Списъкът помни последните филтри и страница: връщане от менюто отваря същото място („?reset=1“ ги чисти).
        $remembered = $request->session()->get('outreach_calls_list_query');
        if ($request->boolean('reset')) {
            $request->session()->forget('outreach_calls_list_query');

            return redirect()->route('admin.outreach.calls.index');
        }
        if ($request->query() === [] && $remembered) {
            return redirect()->route('admin.outreach.calls.index', $remembered);
        }
        $request->session()->put('outreach_calls_list_query', array_filter($request->only(['status', 'city', 'industry', 'phone', 'mobile', 'due', 'page'])) ?: null);

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
     * Текущият бизнес е в адреса (?call=), така че обновяването на страницата го пази; без него се отваря
     * последният незавършен от сесията или първият в опашката. Филтрите (град, вид, мобилен) също са в адреса.
     * „Пропусни“ го скрива до края на сесията; „Назад“ връща предишния (OutreachCallController::back).
     */
    public function next(Request $request)
    {
        $filters = $this->filters($request);
        $session = $request->session();
        $skipped = $session->get('outreach_calls_skipped', []);
        $queue = $this->filtered($filters)->queue()->whereNotIn('id', $skipped);

        if (! $request->filled('call')) {
            // Връщане към страницата: продължаваме от бизнеса, на който сте били, ако още чака обаждане.
            $resume = $session->get('outreach_calls_current');
            $call = ($resume ? (clone $queue)->whereKey($resume)->first() : null) ?? (clone $queue)->first();
            if ($call) {
                return redirect()->route('admin.outreach.calls.next', $this->filterQuery($filters) + ['call' => $call->id]);
            }
        }

        $call = $request->filled('call') ? OutreachCall::find($request->integer('call')) : null;
        if ($request->filled('call') && ! $call) {
            return redirect()->route('admin.outreach.calls.next', $this->filterQuery($filters));
        }
        if ($call) {
            $session->put('outreach_calls_current', $call->id);
        }

        return view('admin.outreach.call-next', [
            'call' => $call,
            'left' => $queue->count(),
            'today' => $this->todayStats(),
            'filters' => $filters,
            'query' => $this->filterQuery($filters),
            'canGoBack' => $session->get('outreach_calls_history', []) !== [],
            // Отворен с „Назад“: показваме записания резултат, за да се поправи.
            'revisit' => $request->boolean('back'),
        ]);
    }

    public function skip(Request $request, OutreachCall $call)
    {
        $request->session()->push('outreach_calls_skipped', $call->id);
        $this->remember($request, $call);

        return redirect()->route('admin.outreach.calls.next', $this->filterQuery($this->filters($request)));
    }

    /** „Назад“: предишният бизнес от „Следващо обаждане“ (запазен или пропуснат), за да се поправи резултатът. */
    public function back(Request $request)
    {
        $filters = $this->filters($request);
        $history = $request->session()->get('outreach_calls_history', []);
        $previous = array_pop($history);
        $request->session()->put('outreach_calls_history', $history);
        if ($previous) {
            // Пропуснатият вече не е скрит, щом се върнем към него.
            $request->session()->put('outreach_calls_skipped', array_values(array_diff($request->session()->get('outreach_calls_skipped', []), [$previous])));
        }

        return redirect()->route('admin.outreach.calls.next', $this->filterQuery($filters) + ($previous ? ['call' => $previous, 'back' => 1] : []));
    }

    /** Историята за „Назад“ (последните 50) и край на „текущия“ бизнес, щом е обработен. */
    private function remember(Request $request, OutreachCall $call): void
    {
        $session = $request->session();
        $history = array_values(array_diff($session->get('outreach_calls_history', []), [$call->id]));
        $history[] = $call->id;
        $session->put('outreach_calls_history', array_slice($history, -50));
        if ($session->get('outreach_calls_current') === $call->id) {
            $session->forget('outreach_calls_current');
        }
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
        // Един бизнес се брои за едно обаждане на ден: поправка на резултата (напр. през „Назад“) не е ново обаждане.
        $calledToday = $call->called_at && $call->called_at->timezone('Europe/Sofia')->toDateString() === OutreachCall::today();
        if ($validated['status'] !== 'new' && ! $calledToday) {
            $call->called_at = now();
            $call->attempts = min(255, $call->attempts + 1);
        }
        $call->save();

        $message = $call->name . ': ' . OutreachCall::STATUSES[$call->status] . ($call->callback_on ? ', ново обаждане на ' . $call->callback_on->format('d.m.Y') : '') . '.';
        if ($request->boolean('next')) {
            $this->remember($request, $call);

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
