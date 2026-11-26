<?php

namespace App\Http\Controllers;

use App\Mail\SiteCheckOfferOrderedMail;
use App\Models\Project;
use App\Support\Notify\Telegram;
use App\Support\SiteCheck\Offers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function Illuminate\Support\defer;

/**
 * Поръчка с един клик от писмата след безплатната проверка (подписан адрес, 60 дни). GET само показва потвърждение,
 * за да не я задейства пощенски скенер, който отваря линковете; поръчката е POST с CSRF. Записва се интерес, не плащане.
 */
class SiteCheckOfferController extends Controller
{
    public function show(Request $request, Project $project, string $offer)
    {
        $this->guard($project, $offer);
        $done = $project->site_check_offer === $offer;

        return gview('site-check-offer', $this->data($project, $offer, $done));
    }

    public function confirm(Request $request, Project $project, string $offer)
    {
        $this->guard($project, $offer);

        // Втори клик върху същата поръчка не прави нищо ново: същата страница „Благодарим“, без нова бележка.
        if ($project->site_check_offer !== $offer) {
            $this->record($project, $offer);
        }

        return gview('site-check-offer', $this->data($project, $offer, true));
    }

    private function guard(Project $project, string $offer): void
    {
        abort_unless(in_array($offer, Offers::KEYS, true) && $project->leadKind() === 'site-check', 404);
    }

    private function data(Project $project, string $offer, bool $done): array
    {
        $other = app()->getLocale() === 'en' ? 'bg' : 'en';

        return [
            'project' => $project,
            'offer' => $offer,
            'text' => Offers::text($offer),
            'done' => $done,
            'domain' => preg_replace('#^https?://#', '', (string) $project->website_url),
            'noindex' => true,
            // Подписаните адреси на същата страница на другия език (само за превключвателя, не за hreflang).
            'pageAlternates' => [app()->getLocale() => request()->fullUrl(), $other => Offers::url($project, $offer, $other)],
            'hreflangAlternates' => [],
        ];
    }

    private function record(Project $project, string $offer): void
    {
        // Бележките и екипът са на български, каквото и да е езикът на клиента.
        $bg = (array) config("creatium.site_check_offers.$offer");
        $name = $bg['name'] ?? $offer;
        $price = Offers::priceLine($offer);

        $values = ['site_check_offer' => $offer, 'site_check_offer_at' => now()];
        // Етапът остава, а следващата стъпка е за днес, освен ако вече има записана.
        if (blank($project->next_action)) {
            $values['next_action'] = $bg['next_action'] ?? ('Свържете се за оферта ' . $name);
            $values['next_action_at'] = now('Europe/Sofia')->endOfDay()->utc();
        }
        $project->forceFill($values)->save();
        $project->notes()->create([
            'user_id' => null,
            'kind' => 'note',
            'body' => 'Клиентът поиска оферта „' . $name . '“ от линка в писмото след проверката (' . strtr((string) ($bg['price_line'] ?? ''), [':price' => Offers::money(Offers::price($offer))]) . '). Още не е платил. Обещахме да му пишем до един работен ден.',
        ]);

        defer(function () use ($project, $offer, $name) {
            try {
                Telegram::send('💶 <b>Поръчка след проверка</b>' . "\n" . e($name) . "\n" . e(preg_replace('#^https?://#', '', (string) $project->website_url)) . "\n✉️ " . e((string) $project->client_email)
                    . "\n" . e(route('admin.projects.show', $project)));
                Mail::to(config('creatium.notify_email'))->send(new SiteCheckOfferOrderedMail($project, $offer));
            } catch (\Throwable $e) {
                Log::error('Site check order notification failed: ' . $e->getMessage(), ['project_id' => $project->id]);
            }
        });
    }
}
