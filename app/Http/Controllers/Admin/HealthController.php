<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\SystemHealth;

/**
 * „Здраве на системата“: всичко, което трябва да е настроено и да работи, на едно място (проверките са в SystemHealth).
 */
class HealthController extends Controller
{
    public function __invoke()
    {
        return view('admin.health', ['checks' => SystemHealth::checks()]);
    }
}
