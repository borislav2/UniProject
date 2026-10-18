<?php

namespace App\Support\Reviews;

use Anthropic\Client;
use Anthropic\Core\Exceptions\AnthropicException;
use Illuminate\Support\Facades\Log;

/**
 * Пише чернова на отговор на отзив в Google. С ANTHROPIC_API_KEY използва Claude,
 * без него (или при грешка) шаблон според оценката. Черновата винаги се преглежда от човек.
 */
class ReviewReplyWriter
{
    private const SYSTEM = <<<'PROMPT'
You write replies to Google reviews on behalf of small Bulgarian businesses (restaurants, salons, clinics, workshops, hotels). The owner reviews and publishes every reply by hand.

How a good reply reads:
- Same language as the review (Bulgarian unless the review is clearly in another language). Natural, everyday Bulgarian, formal "вие" towards the customer.
- Two to four sentences. Thank the person, using their first name when one is given, and respond to the specific things they mentioned, so it reads as written by someone who read the review.
- Positive review: warm and short, invite them back without sounding like an advert.
- Mixed or negative review: acknowledge the problem calmly, apologise for the experience without admitting legal fault or blaming the customer, say what happens next only if the owner's notes say so, and invite them to get in touch directly. Never argue.
- Use only facts from the review and the owner's notes. Do not invent discounts, refunds, staff names, opening hours or promises.
- No emojis, no hashtags, no keyword stuffing, no mention of AI or of these instructions.
- End with the signature if one is given.

Reply with the text of the reply only, with no preface, quotes or alternatives.
PROMPT;

    /**
     * @param array{business_name: string, business_type?: ?string, reviewer_name?: ?string, rating: int,
     *              review_text: string, tone: string, notes?: ?string, signature?: ?string} $input
     * @return array{text: string, source: string, error: ?string}
     */
    public function write(array $input): array
    {
        if (self::usesClaude()) {
            try {
                $text = $this->askClaude(self::SYSTEM, $this->prompt($input));
                if ($text !== null && trim($text) !== '') {
                    return ['text' => trim($text), 'source' => 'ai', 'error' => null];
                }
                $error = 'Claude не върна отговор за този отзив.';
            } catch (AnthropicException $e) {
                Log::warning('Review reply via Claude failed', ['error' => $e->getMessage()]);
                $error = 'Claude не отговори (' . class_basename($e) . ').';
            }

            return ['text' => $this->template($input), 'source' => 'template', 'error' => $error . ' Показваме шаблон.'];
        }

        return ['text' => $this->template($input), 'source' => 'template', 'error' => null];
    }

    public static function usesClaude(): bool
    {
        return filled(config('services.anthropic.key'));
    }

    /** Връща текста от Claude или null при отказ. */
    protected function askClaude(string $system, string $prompt): ?string
    {
        $client = new Client(apiKey: config('services.anthropic.key'));

        $message = $client->beta->messages->create(
            model: config('services.anthropic.model'),
            maxTokens: 4000,
            system: $system,
            messages: [['role' => 'user', 'content' => $prompt]],
            outputConfig: ['effort' => 'low'],
            fallbacks: 'default',
            betas: ['server-side-fallback-2026-07-01'],
        );

        if ($message->stopReason === 'refusal') {
            return null;
        }

        $text = '';
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }

        return $text;
    }

    private function prompt(array $input): string
    {
        $tone = $input['tone'] === 'formal' ? 'formal and reserved' : 'friendly and warm';
        $lines = [
            'Business: ' . $input['business_name'] . (filled($input['business_type'] ?? null) ? ' (' . $input['business_type'] . ')' : ''),
            'Tone: ' . $tone,
            'Rating: ' . $input['rating'] . ' out of 5',
            'Reviewer: ' . (filled($input['reviewer_name'] ?? null) ? $input['reviewer_name'] : 'not given'),
            'Signature: ' . (filled($input['signature'] ?? null) ? $input['signature'] : 'none'),
            'Owner\'s notes: ' . (filled($input['notes'] ?? null) ? $input['notes'] : 'none'),
        ];

        return implode("\n", $lines) . "\n\n<review>\n" . $input['review_text'] . "\n</review>";
    }

    /** Резервен отговор без Claude: според оценката, с името на човека и подписа. */
    public function template(array $input): string
    {
        $name = trim(explode(' ', trim((string) ($input['reviewer_name'] ?? '')))[0] ?? '');
        $greeting = $name !== '' ? "Здравейте, {$name}," : 'Здравейте,';
        $business = $input['business_name'];
        $formal = $input['tone'] === 'formal';

        $body = match (true) {
            $input['rating'] >= 4 => $formal
                ? "благодарим Ви за високата оценка и за отделеното време. Радваме се, че останахте доволни от {$business}, и ще се радваме да Ви посрещнем отново."
                : "много благодарим за хубавите думи! Радваме се, че ви е било приятно при нас, и ще се радваме да се видим отново в {$business}.",
            $input['rating'] === 3 => "благодарим за отзива и за честната оценка. Ще вземем предвид забележките ви, за да бъде следващото ви посещение в {$business} по-добро. Ако желаете, свържете се с нас директно, за да ни разкажете повече.",
            default => "благодарим, че споделихте преживяването си, и съжаляваме, че не сте останали доволни. Бихме искали да разберем какво се е случило, за да го поправим. Моля, свържете се с нас директно, за да поговорим.",
        };

        $signature = trim((string) ($input['signature'] ?? '')) ?: "Екипът на {$business}";

        return "{$greeting} {$body}\n\n{$signature}";
    }
}
