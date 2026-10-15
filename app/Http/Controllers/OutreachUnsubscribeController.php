<?php

namespace App\Http\Controllers;

use App\Models\OutreachProspect;
use App\Models\OutreachSuppression;
use Illuminate\Http\Request;

/** Отписване от първите писма (линкът в писмото и бутонът „Отписване“ в Gmail/Outlook). Подписан адрес, без вход. */
class OutreachUnsubscribeController extends Controller
{
    public function __invoke(Request $request, OutreachProspect $prospect)
    {
        OutreachSuppression::add($prospect->email, 'unsubscribed');
        $prospect->forceFill(['status' => 'unsubscribed', 'unsubscribed_at' => $prospect->unsubscribed_at ?? now()])->save();

        if ($request->isMethod('post')) {
            return response()->noContent();
        }

        return response()->view('outreach-unsubscribed', ['prospect' => $prospect]);
    }
}
