<?php

namespace App\Support\Outreach;

use App\Models\ClientEmail;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\OutreachSuppression;

/** Помощни неща за първите писма до бизнеси, намерени от Harvey. */
class Outreach
{
    /** Изпращането е разрешено само с отделна поща (OUTREACH_MAIL_*), никога през основната. */
    public static function configured(): bool
    {
        return filled(config('creatium.outreach.from_address')) && filled(config('mail.mailers.outreach.host'));
    }

    public static function sentToday(): int
    {
        return ClientEmail::whereNotNull('outreach_prospect_id')->whereNotNull('sent_at')->where('sent_at', '>=', now()->startOfDay())->count();
    }

    public static function remainingToday(): int
    {
        return max(0, config('creatium.outreach.daily_limit') - self::sentToday());
    }

    /**
     * Внася CSV от `harvey export` (колони email, first_name, last_name, company_name, title, website,
     * industry, personalization, email_status, score). Пропуска невалидни, повтарящи се и отписани адреси.
     *
     * @return array{added: int, updated: int, skipped: int}
     */
    public static function import(string $path): array
    {
        $result = ['added' => 0, 'updated' => 0, 'skipped' => 0];

        foreach (self::rows($path) as $data) {
            $email = mb_strtolower(trim((string) ($data['email'] ?? '')));

            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || OutreachSuppression::where('email', $email)->exists()) {
                $result['skipped']++;
                continue;
            }

            $values = array_filter([
                'first_name' => self::clean($data['first_name'] ?? null),
                'last_name' => self::clean($data['last_name'] ?? null),
                'company' => self::clean($data['company_name'] ?? $data['company'] ?? null),
                'title' => self::clean($data['title'] ?? null),
                'website' => self::clean($data['website'] ?? null),
                'industry' => self::clean($data['industry'] ?? null),
                'personalization' => self::clean($data['personalization'] ?? null, 2000),
                'email_status' => self::clean($data['email_status'] ?? null, 20),
                'score' => is_numeric($data['score'] ?? null) ? min(100, max(0, (int) $data['score'])) : null,
            ], fn ($v) => $v !== null);

            $existing = OutreachProspect::where('email', $email)->first();
            if ($existing) {
                // Не пипаме вече изпратените; новите данни от Harvey допълват само непипнатите.
                if ($existing->status === 'new') {
                    $existing->update($values);
                    $result['updated']++;
                } else {
                    $result['skipped']++;
                }
                continue;
            }

            OutreachProspect::create(['email' => $email, 'status' => 'new'] + $values);
            $result['added']++;
        }

        return $result;
    }

    /**
     * Внася бизнесите без сайт (CSV от tools/harvey/export_no_website.py: external_id, name, phone, industry,
     * location, source_url) в списъка за обаждане. Статусът и бележките на вече внесените не се пипат.
     * Бизнесите извън България се пропускат, а вече внесените и непипнати се махат.
     *
     * @return array{added: int, updated: int, skipped: int, foreign: int}
     */
    public static function importCalls(string $path): array
    {
        $result = ['added' => 0, 'updated' => 0, 'skipped' => 0, 'foreign' => 0];

        foreach (self::rows($path) as $data) {
            $id = self::clean($data['external_id'] ?? null);
            $name = self::clean($data['name'] ?? null);
            if ($id === null || $name === null) {
                $result['skipped']++;
                continue;
            }

            $sourceUrl = self::clean($data['source_url'] ?? null);
            $values = [
                'name' => $name,
                'phone' => self::clean($data['phone'] ?? null, 100),
                'industry' => self::clean($data['industry'] ?? null),
                'city' => OutreachCall::cityName(self::clean($data['location'] ?? null)),
                'source_url' => $sourceUrl && preg_match('#^https://#i', $sourceUrl) ? $sourceUrl : null,
            ];

            if (OutreachCall::isForeign($values['phone'], $values['city'])) {
                OutreachCall::where('external_id', $id)->where('status', 'new')->whereNull('note')->delete();
                $result['foreign']++;
                continue;
            }

            $call = OutreachCall::firstOrNew(['external_id' => $id]);
            $result[$call->exists ? 'updated' : 'added']++;
            $call->fill($values)->save();
        }

        return $result;
    }

    /** Редовете на CSV файл като масиви по заглавията (малки букви, без BOM). */
    private static function rows(string $path): \Generator
    {
        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::READ_AHEAD | \SplFileObject::DROP_NEW_LINE);

        $header = null;
        foreach ($file as $row) {
            if ($row === [null] || $row === false) {
                continue;
            }
            if ($header === null) {
                $header = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $row);
                continue;
            }

            yield array_combine($header, array_pad(array_slice($row, 0, count($header)), count($header), ''));
        }
    }

    private static function clean(?string $value, int $max = 255): ?string
    {
        $value = trim(strip_tags((string) $value));

        return $value === '' ? null : mb_substr($value, 0, $max);
    }
}
