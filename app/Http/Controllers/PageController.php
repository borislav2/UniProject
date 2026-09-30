<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function services()
    {
        return view('services', [
            'services' => config('creatium.services'),
            'faq' => config('creatium.faq'),
        ]);
    }

    public function portfolio()
    {
        $projects = Project::with(['category', 'technologies'])
            ->where('is_public', true)
            ->where('status', 'Completed')
            ->orderByDesc('end_date')
            ->get();

        return view('portfolio', compact('projects'));
    }

    public function about()
    {
        return view('about', ['team' => config('creatium.team')]);
    }

    public function privacy()
    {
        return view('privacy', [
            'legal' => config('creatium.legal'),
            'contact' => config('creatium.contact'),
        ]);
    }

    public function terms()
    {
        return view('terms', ['contact' => config('creatium.contact')]);
    }

    public function sitemap(): Response
    {
        $pages = ['home', 'services', 'portfolio', 'about', 'contact', 'privacy', 'terms'];

        return response()
            ->view('sitemap', ['urls' => array_map(fn ($name) => route($name), $pages)])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /login\n\nSitemap: " . route('sitemap') . "\n";

        return response($body)->header('Content-Type', 'text/plain');
    }
}
