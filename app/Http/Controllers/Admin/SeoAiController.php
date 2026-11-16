<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoAiCheck;
use App\Support\Seo\AiPrompts;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** „SEO“ → „AI споменавания“: месечен ръчен дневник дали асистентите ни препоръчват. Нищо не се пуска автоматично. */
class SeoAiController extends Controller
{
    public function index()
    {
        $checks = SeoAiCheck::orderByDesc('checked_on')->orderByDesc('id')->get();
        $assistants = SeoAiCheck::ASSISTANTS;

        // Месец => асистент => [проверки, споменавания]
        $months = [];
        foreach ($checks as $c) {
            $m = $c->checked_on->format('Y-m');
            $cell = $months[$m][$c->assistant] ?? [0, 0];
            $months[$m][$c->assistant] = [$cell[0] + 1, $cell[1] + ($c->mentioned ? 1 : 0)];
        }
        krsort($months);

        $totals = [];
        foreach ($assistants as $a) {
            $mine = $checks->where('assistant', $a);
            $totals[$a] = [$mine->count(), $mine->where('mentioned', true)->count()];
        }

        return view('admin.seo.ai', [
            'checks' => $checks->take(100), 'total' => $checks->count(), 'assistants' => $assistants, 'months' => array_slice($months, 0, 12, true),
            'totals' => $totals, 'prompts' => AiPrompts::SUGGESTED, 'last' => $checks->first()?->checked_on,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'checked_on' => 'required|date|before_or_equal:today',
            'assistant' => ['required', Rule::in(SeoAiCheck::ASSISTANTS)],
            'prompt' => 'required|string|max:500',
            'mentioned' => 'required|in:0,1',
            'position_note' => 'nullable|string|max:300',
            'link' => 'nullable|url:http,https|max:1000',
        ], ['link.url' => 'Връзката трябва да започва с https://', 'checked_on.before_or_equal' => 'Датата не може да е в бъдещето.']);

        SeoAiCheck::create($data + ['user_id' => $request->user()->id]);

        return redirect()->route('admin.seo.ai')->with('success', 'Проверката е записана.');
    }

    public function destroy(SeoAiCheck $check)
    {
        $check->delete();

        return redirect()->route('admin.seo.ai')->with('success', 'Записът е изтрит.');
    }
}
