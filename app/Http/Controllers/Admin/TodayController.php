<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutreachCall;
use App\Support\Admin\Today;

class TodayController extends Controller
{
    public function __invoke(\Illuminate\Http\Request $request)
    {
        // „Моите“: възложените на мен и неразпределените (за да не остане нищо без човек).
        $mine = $request->boolean('mine');
        $only = fn ($items) => $mine ? $items->filter(fn ($p) => ! $p->assigned_to || $p->assigned_to === $request->user()->id)->values() : $items;

        return view('admin.today', [
            'mine' => $mine,
            'due' => $only(Today::due()),
            'unread' => $only(Today::unread()),
            'withoutNextStep' => $only(Today::withoutNextStep()),
            'consultations' => $only(Today::consultations()),
            'callbacks' => Today::callbacks(),
            'calls' => OutreachCall::todayStats(),
            'hot' => Today::hotProspects(),
            'clientIssues' => Today::clientIssues(),
            'followups' => \App\Models\OutreachProspect::dueForFollowUp()->count(),
        ]);
    }
}
