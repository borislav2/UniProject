<?php

namespace App\Http\Controllers;

class DemoController extends Controller
{
    public function show(string $slug)
    {
        $demo = collect(config('creatium.demos'))->firstWhere('slug', $slug);

        abort_unless($demo && view()->exists("demos.$slug"), 404);

        return view("demos.$slug", ['demo' => $demo]);
    }
}
