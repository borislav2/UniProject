<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function services()
    {
        return view('services', [
            'services' => site('services'),
            'faq' => site('faq'),
        ]);
    }

    public function packages()
    {
        return view('packages', [
            'packages' => site('packages'),
            'notes' => site('package_notes'),
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
        return view('about', ['team' => site('team')]);
    }

    public function privacy()
    {
        return view($this->localized('privacy'), [
            'legal' => config('creatium.legal'),
            'contact' => config('creatium.contact'),
        ]);
    }

    public function terms()
    {
        return view($this->localized('terms'), ['contact' => config('creatium.contact')]);
    }

    public function cookies()
    {
        return view($this->localized('cookies'));
    }

    public function sitemap(): Response
    {
        $pages = ['home', 'services', 'packages', 'portfolio', 'about', 'contact', 'privacy', 'terms', 'cookies'];
        $urls = [];

        foreach ($pages as $name) {
            $alternates = ['bg' => lroute($name, [], 'bg'), 'en' => lroute($name, [], 'en')];
            foreach ($alternates as $url) {
                $urls[] = ['loc' => $url, 'alternates' => $alternates];
            }
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /login\n\nSitemap: " . route('sitemap') . "\n";

        return response($body)->header('Content-Type', 'text/plain');
    }

    /** Legal pages are written separately for each language: resources/views/en/* holds the English ones. */
    private function localized(string $view): string
    {
        return app()->getLocale() === 'en' ? "en.$view" : $view;
    }
}
