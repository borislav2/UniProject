<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutreachCall;
use App\Support\Admin\Today;

class TodayController extends Controller
{
    public function __invoke()
    {
        return view('admin.today', [
            'due' => Today::due(),
            'unread' => Today::unread(),
            'withoutNextStep' => Today::withoutNextStep(),
            'consultations' => Today::consultations(),
            'callbacks' => Today::callbacks(),
            'calls' => OutreachCall::todayStats(),
            'hot' => Today::hotProspects(),
        ]);
    }
}
