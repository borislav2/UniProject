<?php

use App\Models\ContentBlock;
use App\Support\Content;
use Illuminate\Database\Migrations\Migration;

/**
 * Владимир's new team photo (19.11.2026). A saved team override keeps its own texts; only an image that is still exactly
 * the old default path is switched to the new file, after a backup to team:backup-2026-11-19 (never overwritten).
 */
return new class extends Migration
{
    public const OLD = 'images/team/vladimir-tsonchev.webp';
    public const NEW = 'images/team/vladimir-tsonchev-2026.webp';

    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        foreach (ContentBlock::where('key', 'team')->get() as $block) {
            $value = $block->value;
            if (! is_array($value)) {
                continue;
            }
            $changed = false;
            foreach ($value as $i => $member) {
                if (is_array($member) && ($member['image'] ?? null) === self::OLD) {
                    $value[$i]['image'] = self::NEW;
                    $changed = true;
                }
            }
            if (! $changed) {
                continue;
            }
            ContentBlock::firstOrCreate(['key' => 'team:backup-2026-11-19', 'locale' => $block->locale], ['value' => $block->value]);
            $block->value = $value;
            $block->save();
        }

        app(Content::class)->flush();
    }

    public function down(): void
    {
    }
};
