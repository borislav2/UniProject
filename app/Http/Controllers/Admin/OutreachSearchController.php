<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutreachBusiness;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\OutreachSearch;
use App\Support\Outreach\BusinessSearch;
use App\Support\Outreach\Markets;
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
            'usesGoogle' => BusinessSearch::usesGoogle(),
            'resettable' => self::resettableProspects()->count() + OutreachBusiness::count() + OutreachCall::count(),
        ]);
    }

    /**
     * Започване на чисто: трие търсенията, намерените бизнеси, списъка за обаждане и контактите, на които
     * още не е писано. Остават бизнесите, на които е изпратено писмо, и отписаните (никога не им пишем пак).
     */
    public function reset()
    {
        $prospects = self::resettableProspects()->delete();
        $calls = OutreachCall::query()->delete();
        OutreachBusiness::query()->delete();
        OutreachSearch::query()->delete();

        return redirect()->route('admin.outreach.search.index')->with('success', "Изчистено: {$prospects} контакта без изпратено писмо и {$calls} от списъка за обаждане. Изпратените и отписаните са запазени.");
    }

    private static function resettableProspects()
    {
        return OutreachProspect::whereIn('status', ['new', 'skipped', 'routed'])->whereNull('sent_at');
    }

    public function store(Request $request)
    {
        $country = Markets::normalize($request->input('country'));
        $validated = $request->validate([
            'city' => ['required', Rule::in(array_keys(Markets::cities($country)))],
            'category' => ['required', Rule::in(array_keys(BusinessSearch::CATEGORIES))],
        ]);
        $validated['country'] = $country;

        $search = OutreachSearch::where($validated)->whereIn('status', ['queued', 'processing'])->first()
            ?? OutreachSearch::create($validated + ['language' => Markets::language($country), 'user_id' => $request->user()->id]);

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
