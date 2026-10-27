<?php

namespace App\Support;

/** Записва настройки в .env от командите за свързване (Facebook, Telegram), без да пипа останалите редове. */
class EnvFile
{
    public static function set(array $values, ?string $path = null): void
    {
        $path ??= base_path('.env');
        $lines = is_file($path) ? preg_split('/\r?\n/', rtrim((string) file_get_contents($path), "\n")) : [];
        foreach ($values as $key => $value) {
            $lines = array_values(array_filter($lines, fn ($line) => ! preg_match('/^#?\s*' . preg_quote($key, '/') . '=/', $line)));
            $lines[] = $key . '="' . str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $value) . '"';
        }
        file_put_contents($path, implode("\n", $lines) . "\n");
    }
}
