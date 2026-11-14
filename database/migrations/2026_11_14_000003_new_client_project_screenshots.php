<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Собствениците (14.11.2026): на картите на lasepro.bg и pokrivi-burgas.eu в /proekti стоеше снимка от старата им визия.
// Новата снимка е с ново име на файла, за да не се показва старата от кеша. Заменя се снимката само на тези два проекта
// (разпознати по сайта или по името), без значение коя е била (качена от админа или от миграцията от 10.11.2026).
// Старите файлове остават на диска, а предишната стойност се записва в лога.
return new class extends Migration
{
    public const SHOTS = [
        'images/projects/lasepro-2026-11.webp' => ['lasepro'],
        'images/projects/pokrivi-burgas-2026-11.webp' => ['pokrivi-burgas', 'pokrivi burgas'],
    ];

    public function up(): void
    {
        foreach (self::SHOTS as $path => $needles) {
            $rows = DB::table('projects')
                ->where(function ($q) use ($needles) {
                    foreach ($needles as $needle) {
                        $q->orWhereRaw('LOWER(website_url) LIKE ?', ['%' . $needle . '%'])
                            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $needle . '%']);
                    }
                })
                ->where(fn ($q) => $q->whereNull('screenshot')->orWhere('screenshot', '!=', $path))
                ->get(['id', 'screenshot']);

            foreach ($rows as $row) {
                Log::info('Снимка на проект ' . $row->id . ' сменена: ' . ($row->screenshot ?: '(празна)') . ' -> ' . $path);
                DB::table('projects')->where('id', $row->id)->update(['screenshot' => $path]);
            }
        }
    }

    public function down(): void
    {
        // Предишните стойности са в лога; старите файлове не са изтривани.
    }
};
