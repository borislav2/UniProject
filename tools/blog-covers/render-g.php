<?php

// Превръща PNG от render-g.mjs в WebP: public/images/blog/g/{slug}.webp (1200x675) и {slug}-640.webp (640x360).
//   php tools/blog-covers/render-g.php <папка с PNG> [quality]
$in = rtrim($argv[1] ?? __DIR__.'/.out-g', '/');
$quality = (int) ($argv[2] ?? 82);
$out = dirname(__DIR__, 2).'/public/images/blog/g';
if (! is_dir($out)) {
    mkdir($out, 0775, true);
}

foreach (glob($in.'/*.png') as $file) {
    $name = basename($file, '.png');
    $img = imagecreatefrompng($file);
    imagewebp($img, "$out/$name.webp", $quality);
    $small = imagescale($img, 640, 360, IMG_BICUBIC);
    imagewebp($small, "$out/$name-640.webp", $quality);
    printf("%s %d KB / %d KB\n", $name, filesize("$out/$name.webp") / 1024, filesize("$out/$name-640.webp") / 1024);
}
