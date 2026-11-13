<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Мария Флорова (13.11.2026): новите текстове от нея. TeamMaria2026 вече е дописала СТАРИТЕ текстове от config в запазените
 * редакции на `team`. Тук се сменят само полетата (description, bio, skills), които още са точно старата стойност по подразбиране.
 * Първо редът се копира в `team:backup-2026-11-13-b` (без да се презаписва съществуващо копие). Другите членове и
 * полетата, които админът е променил, не се пипат.
 */
class TeamMariaTexts2026
{
    private const OLD = [
        'bg' => [
            'description' => 'Грижа се бизнесът ви да изглежда и да звучи еднакво навсякъде, от логото до всеки имейл и всяка публикация.',
            'bio' => [
                'Моята част е лицето на бизнеса ви. Изграждам бранд идентичността ви, а това са логото, цветовете и тонът, с който говорите на клиентите. Така хората ви разпознават и ви помнят, където и да ви срещнат.',
                'Правя и имейл кампании, които връщат клиентите при вас, и поддържам профилите ви в социалните мрежи. Публикациите са в духа на бранда ви, а после гледам какво носят и какво е по-добре да променим.',
            ],
            'skills' => ['Разпознаваем бранд', 'Имейл кампании', 'Социални мрежи', 'Единен тон навсякъде'],
        ],
        'en' => [
            'description' => 'I make sure your business looks and sounds the same everywhere, from the logo to every email and every post.',
            'bio' => [
                'My part is the face of your business. I build your brand identity, which means the logo, the colours and the tone you use with customers. That way people recognise you and remember you wherever they meet you.',
                'I also run email campaigns that bring customers back to you and look after your social media profiles. The posts match your brand, and afterwards I check what they bring and what we should change.',
            ],
            'skills' => ['A recognisable brand', 'Email campaigns', 'Social media', 'One tone everywhere'],
        ],
    ];

    /** @return list<string> променените записи като „ключ/език“ */
    public static function apply(): array
    {
        $changed = [];

        foreach (ContentBlock::where('key', 'team')->get() as $block) {
            $locale = (string) $block->locale;
            $old = self::OLD[$locale] ?? null;
            $new = TeamMaria2026::entry($locale);
            if ($old === null || $new === null) {
                continue;
            }

            $team = array_values((array) $block->value);
            $touched = false;
            $updated = $team;

            foreach ($team as $i => $member) {
                if (! is_array($member) || ! TeamMaria2026::hasMaria([$member])) {
                    continue;
                }
                foreach (['description', 'bio', 'skills'] as $field) {
                    if (array_key_exists($field, $member) && $member[$field] === $old[$field] && $new[$field] !== $old[$field]) {
                        $updated[$i][$field] = $new[$field];
                        $touched = true;
                    }
                }
            }

            if (! $touched) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => 'team:backup-2026-11-13-b', 'locale' => $block->locale],
                ['value' => $block->value]
            );
            $block->update(['value' => $updated]);
            $changed[] = 'team/' . $locale;
        }

        if ($changed) {
            app(Content::class)->flush();
        }

        return $changed;
    }
}
