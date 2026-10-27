<?php

namespace App\Http\Controllers;

use App\Mail\OutreachMail;
use App\Models\OutreachProspect;
use Illuminate\Http\Request;

/**
 * Клик по линк в първото писмо: записва го при бизнеса и препраща към страницата. Подписан адрес, без вход.
 * Не броим нас (влезли в админа), ботове и скенери на пощата, нито кликове в първата минута след изпращането,
 * когато корпоративните филтри „отварят“ всички линкове сами.
 */
class OutreachClickController extends Controller
{
    private const BOTS = '/bot|crawl|spider|preview|scan|safelinks|proofpoint|mimecast|barracuda|headless|python|curl|wget|java\/|go-http|okhttp/i';

    public function __invoke(Request $request, OutreachProspect $prospect, string $link)
    {
        $targets = OutreachMail::targets();
        abort_unless(isset($targets[$link]), 404);

        if ($this->counts($request, $prospect)) {
            $prospect->forceFill([
                'clicked_at' => $prospect->clicked_at ?? now(),
                'last_clicked_at' => now(),
                'click_count' => $prospect->click_count + 1,
                'last_click_link' => $link,
            ])->save();
        }

        return redirect()->away($targets[$link]);
    }

    private function counts(Request $request, OutreachProspect $prospect): bool
    {
        $agent = (string) $request->userAgent();

        return $request->isMethod('get')
            && ! $request->user()
            && $agent !== '' && ! preg_match(self::BOTS, $agent)
            && ! ($prospect->sent_at && $prospect->sent_at->gt(now()->subMinute()));
    }
}
