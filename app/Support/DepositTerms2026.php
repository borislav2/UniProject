<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Собствениците (08.11.2026): капарото не се връща, работният капацитет е на месец.
 * Допълва само точните текстове, които миграцията с окончателните цени е записала в админ редакциите
 * на `plans` и `questions`; всеки променен запис първо се копира в `<ключ>:backup-2026-11-08-b`.
 */
class DepositTerms2026
{
    public const REPLACE = [
        '€100 при заявка за оферта и демо, останалите €300 при съгласие.' => '€100 при заявка за оферта и демо (не се връщат), останалите €300 при съгласие.',
        '€200 при заявка за оферта и демо, останалите €600 при съгласие.' => '€200 при заявка за оферта и демо (не се връщат), останалите €600 при съгласие.',
        'Първата сума се плаща преди да подготвим офертата и демото.' => 'Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите.',
        '€100 when you request a quote and demo, the remaining €300 when you agree.' => '€100 when you request a quote and demo (non-refundable), the remaining €300 when you agree.',
        '€200 when you request a quote and demo, the remaining €600 when you agree.' => '€200 when you request a quote and demo (non-refundable), the remaining €600 when you agree.',
        'The first amount is paid before we prepare the quote and the demo.' => 'The first amount is paid before we prepare the quote and the demo and is not refunded if you decide not to continue.',
    ];

    /** Етикети, които се сменят само когато са точно старият текст. */
    public const LABELS = [
        'Работен капацитет' => 'Работен капацитет на месец',
        'Working capacity' => 'Working capacity per month',
    ];

    /** @return list<string> променените записи като „ключ/език“ */
    public static function apply(): array
    {
        $changed = [];

        foreach (ContentBlock::whereIn('key', ['plans', 'questions'])->get() as $block) {
            $value = self::transform((array) $block->value);
            if ($value === $block->value) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => $block->key . ':backup-2026-11-08-b', 'locale' => $block->locale],
                ['value' => $block->value]
            );
            $block->update(['value' => $value]);
            $changed[] = $block->key . '/' . $block->locale;
        }

        if ($changed) {
            app(Content::class)->flush();
        }

        return $changed;
    }

    public static function transform(array $value): array
    {
        array_walk_recursive($value, function (&$item, $key) {
            if (! is_string($item)) {
                return;
            }
            if ($key === 'label' && isset(self::LABELS[$item])) {
                $item = self::LABELS[$item];

                return;
            }
            // Не допълваме втори път текст, който вече казва, че сумата не се връща.
            foreach (self::REPLACE as $old => $new) {
                if (! str_contains($item, $new)) {
                    $item = str_replace($old, $new, $item);
                }
            }
        });

        return $value;
    }
}
