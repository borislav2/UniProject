<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Blog covers in design G: the starter covers (public/images/blog/<name>.webp) have G drawings in public/images/blog/g/ (full size and a
 * 640 px copy for cards). Design G uses them when the file exists and falls back to the original cover otherwise; uploaded covers stay as they are;
 * the current design keeps the old path.
 */
class BlogCoversGTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(string $slug, ?string $cover): Post
    {
        return Post::create([
            'title' => ['bg' => 'Статия ' . $slug],
            'slug' => ['bg' => $slug],
            'body' => ['bg' => 'Текст.'],
            'cover_image' => $cover,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_every_starter_cover_has_a_g_drawing_in_both_sizes(): void
    {
        $covers = glob(public_path('images/blog/*.webp'));
        $this->assertNotEmpty($covers);

        foreach ($covers as $cover) {
            $name = basename($cover, '.webp');
            foreach (["images/blog/g/{$name}.webp" => [1200, 675], "images/blog/g/{$name}-640.webp" => [640, 360]] as $file => [$w, $h]) {
                $this->assertFileExists(public_path($file));
                $this->assertSame([$w, $h], array_slice(getimagesize(public_path($file)), 0, 2), $file);
                $this->assertLessThan(60 * 1024, filesize(public_path($file)), $file . ' is over 60 KB');
            }
        }
    }

    public function test_the_helper_picks_the_g_file_and_falls_back(): void
    {
        $this->assertStringStartsWith('images/blog/g/kakvo-e-geo.webp?v=', g_cover('images/blog/kakvo-e-geo.webp'));
        $this->assertStringStartsWith('images/blog/g/kakvo-e-geo-640.webp?v=', g_cover('images/blog/kakvo-e-geo.webp', true));
        // No G drawing: the original path.
        $this->assertSame('images/blog/bez-g-risunka.webp', g_cover('images/blog/bez-g-risunka.webp'));
        $this->assertSame('images/blog/bez-g-risunka.webp', g_cover('images/blog/bez-g-risunka.webp', true));
        $this->assertNull(g_cover_srcset('images/blog/bez-g-risunka.webp'));
        // Uploads and foreign paths are never touched, even when a file with the same base name exists in the g folder.
        $this->assertSame('uploads/blog/kakvo-e-geo.webp', g_cover('uploads/blog/kakvo-e-geo.webp'));
        $this->assertNull(g_cover_srcset('uploads/blog/kakvo-e-geo.webp'));
        $this->assertSame('images/blog/g/kakvo-e-geo.webp', g_cover('images/blog/g/kakvo-e-geo.webp'));
        $this->assertNull(g_cover(null));
        $this->assertNull(g_cover(''));
    }

    public function test_design_g_cards_and_the_post_use_the_g_cover(): void
    {
        config(['creatium.design' => 'g']);
        $this->makePost('s-g-risunka', 'images/blog/kakvo-e-geo.webp');

        $list = $this->get('/blog')->assertOk()->getContent();
        $this->assertStringContainsString('images/blog/g/kakvo-e-geo-640.webp?v=', $list);
        $this->assertStringContainsString('srcset="', $list);
        $this->assertStringContainsString('images/blog/g/kakvo-e-geo.webp?v=', $list);
        $this->assertStringNotContainsString('src="' . asset('images/blog/kakvo-e-geo.webp') . '"', $list);

        $show = $this->get('/blog/s-g-risunka')->assertOk()->getContent();
        $this->assertStringContainsString('<img src="' . asset(g_cover('images/blog/kakvo-e-geo.webp')) . '" alt="Статия s-g-risunka" width="1200" height="675"', $show);
        // The BlogPosting image keeps the original cover.
        $this->assertStringContainsString('"image":"' . asset('images/blog/kakvo-e-geo.webp') . '"', $show);
    }

    public function test_design_g_falls_back_to_the_original_and_keeps_uploads(): void
    {
        config(['creatium.design' => 'g']);
        $this->makePost('bez-risunka', 'images/blog/bez-g-risunka.webp');
        $this->makePost('kachena', 'uploads/blog/moya-korica.webp');
        $this->makePost('bez-korica', null);

        $list = $this->get('/blog')->assertOk()->getContent();
        $this->assertStringContainsString('src="' . asset('images/blog/bez-g-risunka.webp') . '"', $list);
        $this->assertStringNotContainsString('bez-g-risunka-640', $list);
        $this->assertStringContainsString('src="' . asset('uploads/blog/moya-korica.webp') . '"', $list);
        // A post without a cover gets the placeholder.
        $this->assertStringContainsString('class="g-noimg g-mono"', $list);

        $this->get('/blog/kachena')->assertOk()
            ->assertSee('<img src="' . asset('uploads/blog/moya-korica.webp') . '" alt="Статия kachena"', false);
        $this->get('/blog/bez-risunka')->assertOk()
            ->assertSee('<img src="' . asset('images/blog/bez-g-risunka.webp') . '" alt="Статия bez-risunka"', false);
    }

    public function test_the_current_design_keeps_the_old_cover_path(): void
    {
        config(['creatium.design' => 'current']);
        $this->makePost('stara', 'images/blog/kakvo-e-geo.webp');

        $list = $this->get('/blog')->assertOk()->getContent();
        $this->assertStringContainsString(asset('images/blog/kakvo-e-geo.webp'), $list);
        $this->assertStringNotContainsString('images/blog/g/', $list);

        $show = $this->get('/blog/stara')->assertOk()->getContent();
        $this->assertStringContainsString(asset('images/blog/kakvo-e-geo.webp'), $show);
        $this->assertStringNotContainsString('images/blog/g/', $show);
    }
}
