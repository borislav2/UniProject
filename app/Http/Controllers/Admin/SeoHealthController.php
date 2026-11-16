<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoReport;
use App\Support\Seo\SiteHealth;

/** „SEO“ → „SEO здраве“: резултатът от вътрешното обхождане и бутонът „Провери сега“. */
class SeoHealthController extends Controller
{
    public function index()
    {
        $report = SiteHealth::latest();

        return view('admin.seo.health', [
            'report' => $report,
            'trend' => SiteHealth::trend(),
            'history' => SeoReport::orderByDesc('ran_at')->orderByDesc('id')->limit(12)->get(['id', 'ran_at', 'score', 'errors', 'warnings', 'pages'])->reverse()->values(),
            'running' => SiteHealth::running(),
            'state' => SiteHealth::state(),
        ]);
    }

    /** Проверката върви след отговора (отнема от няколко секунди до около минута), екранът се опреснява ръчно. */
    public function run()
    {
        if (SiteHealth::running()) {
            return redirect()->route('admin.seo.health')->with('error', 'Проверката вече върви. Опреснете страницата след малко.');
        }
        defer(fn () => SiteHealth::runLocked());

        return redirect()->route('admin.seo.health')->with('success', 'Проверката тръгна. Отнема от няколко секунди до около минута: опреснете страницата след малко.');
    }
}
