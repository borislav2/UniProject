<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Images uploaded from the admin panel live in public/uploads/<dir> (backed up by deploy/backup.sh).
 * Paths are stored relative to public/, e.g. "uploads/blog/20261004-120000-ab12cd34.jpg".
 */
class Uploads
{
    /** No SVG: it can carry scripts. */
    public const IMAGE_RULE = 'image|mimes:jpg,jpeg,png,webp,gif|max:5120';

    public static function storeImage(UploadedFile $file, string $dir): string
    {
        File::ensureDirectoryExists(public_path("uploads/$dir"));

        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        $name = now()->format('Ymd-His') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
        $file->move(public_path("uploads/$dir"), $name);

        return "uploads/$dir/$name";
    }

    public static function delete(?string $path): void
    {
        if (self::isUpload($path) && is_file(public_path($path))) {
            @unlink(public_path($path));
        }
    }

    /** Only paths we created ourselves are accepted back from forms. */
    public static function isUpload(?string $path): bool
    {
        return is_string($path) && preg_match('#^uploads/[a-z]+/[A-Za-z0-9._-]+$#', $path) === 1;
    }
}
