<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoKeyword;
use App\Models\SeoKeywordPosition;
use App\Support\Seo\KeywordImport;
use App\Support\Seo\KeywordTargets;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** „SEO“ → „Ключови думи“: ръчно следене на позиции (няма API за позиции) и внасяне от Search Console. */
class SeoKeywordController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->query('locale');
        $keywords = SeoKeyword::with('positions')
            ->when(in_array($locale, array_keys(SeoKeyword::LOCALES), true), fn ($q) => $q->where('locale', $locale))
            ->get()
            ->sortBy(fn (SeoKeyword $k) => [array_search($k->priority, array_keys(SeoKeyword::PRIORITIES)), $k->last()?->position ?? 999, $k->keyword])
            ->values();

        return view('admin.seo.keywords', ['keywords' => $keywords, 'targets' => KeywordTargets::options(), 'locale' => $locale]);
    }

    private function rules(): array
    {
        return [
            'keyword' => 'required|string|max:200',
            'locale' => ['required', Rule::in(array_keys(SeoKeyword::LOCALES))],
            'country' => ['required', Rule::in(array_keys(SeoKeyword::COUNTRIES))],
            'target' => 'nullable|string|max:200',
            'priority' => ['required', Rule::in(array_keys(SeoKeyword::PRIORITIES))],
            'note' => 'nullable|string|max:500',
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['keyword'] = trim(preg_replace('/\s+/u', ' ', $data['keyword']));
        $exists = SeoKeyword::where('locale', $data['locale'])->where('country', $data['country'])->get()->contains(fn ($k) => SeoKeyword::normalize($k->keyword) === SeoKeyword::normalize($data['keyword']));
        if ($exists) {
            return back()->withInput()->with('error', 'Тази дума вече се следи за този език и държава.');
        }
        SeoKeyword::create($data);

        return redirect()->route('admin.seo.keywords')->with('success', 'Думата е добавена.');
    }

    public function update(Request $request, SeoKeyword $keyword)
    {
        $keyword->update($request->validate($this->rules()));

        return redirect()->route('admin.seo.keywords')->with('success', 'Промените са запазени.');
    }

    public function destroy(SeoKeyword $keyword)
    {
        $keyword->delete();

        return redirect()->route('admin.seo.keywords')->with('success', 'Думата е премахната.');
    }

    /** Един ръчен запис: дата, позиция и по желание показвания и кликвания от Search Console. */
    public function position(Request $request, SeoKeyword $keyword)
    {
        $data = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'position' => 'nullable|numeric|min:0.1|max:1000',
            'impressions' => 'nullable|integer|min:0|max:100000000',
            'clicks' => 'nullable|integer|min:0|max:100000000',
        ], ['date.before_or_equal' => 'Датата не може да е в бъдещето.']);
        if (! filled($data['position'] ?? null) && ! filled($data['impressions'] ?? null) && ! filled($data['clicks'] ?? null)) {
            return back()->with('error', 'Напишете позиция, показвания или кликвания.');
        }
        SeoKeywordPosition::updateOrCreate(
            ['seo_keyword_id' => $keyword->id, 'date' => $data['date']],
            ['position' => $data['position'] ?? null, 'impressions' => $data['impressions'] ?? null, 'clicks' => $data['clicks'] ?? null],
        );

        return redirect()->route('admin.seo.keywords')->with('success', 'Записано за „' . $keyword->keyword . '“.');
    }

    public function destroyPosition(SeoKeyword $keyword, SeoKeywordPosition $position)
    {
        abort_unless($position->seo_keyword_id === $keyword->id, 404);
        $position->delete();

        return redirect()->route('admin.seo.keywords')->with('success', 'Записът е изтрит.');
    }

    /** Поставено от Search Console „Заявки“: обновява позициите за избраната дата (по подразбиране днес). */
    public function import(Request $request)
    {
        $data = $request->validate([
            'csv' => 'required|string|max:400000',
            'date' => 'required|date|before_or_equal:today',
            'locale' => ['required', Rule::in(array_keys(SeoKeyword::LOCALES))],
            'country' => ['required', Rule::in(array_keys(SeoKeyword::COUNTRIES))],
        ]);
        $parsed = KeywordImport::parse($data['csv']);
        if (! $parsed['rows']) {
            return back()->withInput()->with('error', 'Не разпознах редове със заявки. Поставете експорта от Search Console („Заявки“) заедно със заглавния ред.');
        }
        $result = KeywordImport::apply($parsed, $data['locale'], $data['country'], $data['date'], $request->boolean('add_unknown'));

        $message = "Обновени позиции: {$result['updated']}";
        if ($result['created']) {
            $message .= ", нови думи: {$result['created']}";
        }
        if ($result['unknown']) {
            $message .= '. Пропуснати непознати думи: ' . count($result['unknown']) . ' (напр. ' . implode(', ', array_slice($result['unknown'], 0, 3)) . ')';
        }

        return redirect()->route('admin.seo.keywords')->with('success', $message . '.');
    }
}
