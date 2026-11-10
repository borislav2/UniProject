<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Собствениците (10.11.2026): снимки на първия екран на lasepro.bg и pokrivi-burgas.eu за картите на /proekti.
// Слагат се само на проекти без снимка, разпознати по сайта или по името; качена от админа снимка не се пипа.
return new class extends Migration
{
    public const SHOTS = [
        'images/projects/lasepro.webp' => ['lasepro'],
        'images/projects/pokrivi-burgas.webp' => ['pokrivi-burgas', 'pokrivi burgas'],
    ];

    public function up(): void
    {
        foreach (self::SHOTS as $path => $needles) {
            DB::table('projects')
                ->where(fn ($q) => $q->whereNull('screenshot')->orWhere('screenshot', ''))
                ->where(function ($q) use ($needles) {
                    foreach ($needles as $needle) {
                        $q->orWhereRaw('LOWER(website_url) LIKE ?', ['%' . $needle . '%'])
                            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $needle . '%']);
                    }
                })
                ->update(['screenshot' => $path]);
        }
    }

    public function down(): void
    {
        DB::table('projects')->whereIn('screenshot', array_keys(self::SHOTS))->update(['screenshot' => null]);
    }
};
