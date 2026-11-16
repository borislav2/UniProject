<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\Help;

/** „Как работи админът“: всички раздели с обяснение какво са и защо ги има. Само чете, нищо не променя. */
class HelpController extends Controller
{
    public function __invoke()
    {
        return view('admin.help', ['sections' => Help::sections()]);
    }
}
