<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutreachSearch;
use App\Support\Outreach\BusinessSearch;
use App\Support\Outreach\ProspectFinder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** „Потенциални клиенти“ → „Търсене“: град + вид бизнес, търсенето върви на части, докато страницата е отворена. */
class OutreachSearchController extends Controller
{
    public function index()
    {
        return view('admin.outreach.search', [
            'searches' => OutreachSearch::latest()->limit(30)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city' => ['required', Rule::in(array_keys(BusinessSearch::CITIES))],
            'category' => ['required', Rule::in(array_keys(BusinessSearch::CATEGORIES))],
        ]);

        $search = OutreachSearch::where($validated)->whereIn('status', ['queued', 'processing'])->first()
            ?? OutreachSearch::create($validated + ['user_id' => $request->user()->id]);

        return redirect()->route('admin.outreach.search.index')->with('success', 'Търсенето започна: ' . $search->label() . '. Оставете страницата отворена, докато приключи.');
    }

    /** Една порция работа (до ~25 секунди); страницата я вика отново, докато търсенето приключи. */
    public function work(OutreachSearch $search, ProspectFinder $finder)
    {
        ignore_user_abort(true);
        set_time_limit(180);

        return response()->json($finder->work($search));
    }
}
