<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Outreach\Autopilot;
use App\Support\Outreach\BusinessSearch;
use App\Support\Outreach\Markets;
use Illuminate\Http\Request;

/** „Автопилот“: пауза, лимити и план (кои пазари, градове и видове бизнес се търсят). Включва се с OUTREACH_AUTOPILOT=true в .env. */
class OutreachAutopilotController extends Controller
{
    public function index()
    {
        return view('admin.outreach.autopilot', [
            'switchedOn' => (bool) config('creatium.outreach.autopilot.enabled'),
            'settings' => Autopilot::settings(),
            'state' => Autopilot::state(),
            'usage' => [
                'new' => Autopilot::newToday(),
                'searches' => Autopilot::searchesToday(),
                'all_searches' => Autopilot::searchesToday(false),
                'pages' => Autopilot::pagesToday(),
                'waiting' => Autopilot::waiting(),
            ],
            'google' => BusinessSearch::usesGoogle(),
            'inHours' => Autopilot::inHours(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'daily_new' => 'required|integer|min:0|max:200',
            'daily_searches' => 'required|integer|min:0|max:50',
            'plan' => 'nullable|array',
        ]);

        $plan = [];
        foreach (Markets::codes() as $code) {
            $row = $validated['plan'][$code] ?? [];
            $cities = array_values(array_intersect(array_keys(Markets::cities($code)), (array) ($row['cities'] ?? [])));
            $plan[$code] = [
                'enabled' => ! empty($row['enabled']),
                // Всички градове избрани = празно (всички, включително бъдещите).
                'cities' => count($cities) === count(Markets::cities($code)) ? [] : $cities,
                'categories' => array_values(array_intersect(array_keys(BusinessSearch::CATEGORIES), (array) ($row['categories'] ?? []))),
            ];
        }

        Autopilot::saveSettings([
            'paused' => $request->boolean('paused'),
            'daily_new' => (int) $validated['daily_new'],
            'daily_searches' => (int) $validated['daily_searches'],
            'plan' => $plan,
        ]);

        return redirect()->route('admin.outreach.autopilot')->with('success', 'Настройките на автопилота са запазени.');
    }

    /** Една стъпка веднага (търси и подготвя чернови, никога не праща писма). */
    public function run(Autopilot $autopilot)
    {
        ignore_user_abort(true);
        set_time_limit(120);

        $result = $autopilot->tick(force: true);
        $labels = [
            'off' => 'Автопилотът е изключен или на пауза.', 'busy' => 'Друга стъпка върви в момента.', 'daily_new' => 'Дневният лимит за нови бизнеси е достигнат.',
            'daily_searches' => 'Дневният лимит за търсения е достигнат.', 'nothing_to_search' => 'Няма какво да се търси по плана (всичко е търсено наскоро).',
            'error' => 'Грешка: ' . ($result['error'] ?? ''),
        ];

        return redirect()->route('admin.outreach.autopilot')->with(in_array($result['status'], ['error', 'off'], true) ? 'send_error' : 'success', $labels[$result['status']] ?? ('Стъпката е направена' . (isset($result['search']) ? ': ' . $result['search'] : '') . '.'));
    }
}
