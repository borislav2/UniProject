<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Support\Content;
use App\Support\Uploads;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function __construct(private Content $content)
    {
    }

    public function index()
    {
        $filled = ContentBlock::where('key', 'like', 'seo:%')->get()
            ->groupBy(fn ($b) => substr($b->key, 4))
            ->map(fn ($rows) => $rows->filter(fn ($b) => array_filter((array) $b->value, 'filled'))->pluck('locale')->all());

        return view('admin.seo.index', ['pages' => Content::SEO_PAGES, 'filled' => $filled]);
    }

    public function edit(string $page)
    {
        abort_unless(isset(Content::SEO_PAGES[$page]), 404);

        return view('admin.seo.edit', [
            'page' => $page,
            'label' => Content::SEO_PAGES[$page],
            'values' => ['bg' => $this->content->seo($page, 'bg'), 'en' => $this->content->seo($page, 'en')],
            'urls' => ['bg' => lroute($page, [], 'bg'), 'en' => lroute($page, [], 'en')],
        ]);
    }

    public function update(Request $request, string $page)
    {
        abort_unless(isset(Content::SEO_PAGES[$page]), 404);

        $rules = [];
        foreach (['bg', 'en'] as $l) {
            $rules += [
                "seo.$l.meta_title" => 'nullable|string|max:120',
                "seo.$l.meta_description" => 'nullable|string|max:300',
                "seo.$l.canonical_url" => 'nullable|url:http,https|max:500',
                "seo.$l.og_title" => 'nullable|string|max:200',
                "seo.$l.og_description" => 'nullable|string|max:300',
                "og_image.$l" => 'nullable|' . Uploads::IMAGE_RULE,
            ];
        }
        $data = $request->validate($rules, ['seo.*.canonical_url.url' => 'Canonical трябва да е пълен адрес, започващ с https://']);

        foreach (['bg', 'en'] as $l) {
            $current = $this->content->seo($page, $l);
            $values = [];
            foreach (['meta_title', 'meta_description', 'canonical_url', 'og_title', 'og_description'] as $field) {
                $values[$field] = trim((string) ($data['seo'][$l][$field] ?? '')) ?: null;
            }

            $values['og_image'] = $current['og_image'] ?? null;
            if ($request->hasFile("og_image.$l")) {
                Uploads::delete($values['og_image']);
                $values['og_image'] = Uploads::storeImage($request->file("og_image.$l"), 'content');
            } elseif ($request->boolean("remove_og_image.$l")) {
                Uploads::delete($values['og_image']);
                $values['og_image'] = null;
            }

            if (array_filter($values)) {
                $this->content->save("seo:$page", $l, $values, $request->user()->id);
            } else {
                $this->content->reset("seo:$page", $l);
            }
        }

        return redirect()->route('admin.seo.edit', $page)->with('success', 'SEO настройките са запазени.');
    }
}
