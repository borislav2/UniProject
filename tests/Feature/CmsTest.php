<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CmsTest extends TestCase
{
    use RefreshDatabase;

    private array $existingUploads = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->existingUploads = $this->uploads();
    }

    protected function tearDown(): void
    {
        // Remove only the images this test uploaded.
        File::delete(array_diff($this->uploads(), $this->existingUploads));
        parent::tearDown();
    }

    private function uploads(): array
    {
        return array_merge(File::glob(public_path('uploads/blog/*')), File::glob(public_path('uploads/content/*')));
    }

    private function editor(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['password' => Hash::make('old-password-123')]);
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));

        return $user;
    }

    private function makePost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'title' => ['bg' => 'Как да изберете домейн', 'en' => 'How to choose a domain'],
            'slug' => ['bg' => 'kak-da-izberete-domeyn', 'en' => 'how-to-choose-a-domain'],
            'body' => ['bg' => "## Първо\n\nТекст **удебелен**.\n\n<script>alert(1)</script>", 'en' => 'English **body**.'],
            'status' => 'published',
            'published_at' => now()->subDay(),
        ], $overrides));
    }

    public function test_guests_cannot_reach_cms(): void
    {
        foreach (['/admin/posts', '/admin/content', '/admin/seo', '/admin/profile', '/admin/post-categories'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->editor());
        $post = $this->makePost();
        $category = PostCategory::create(['name' => ['bg' => 'SEO'], 'slug' => ['bg' => 'seo']]);

        foreach (['/admin/posts', '/admin/posts/create', "/admin/posts/{$post->id}/edit", '/admin/post-categories', "/admin/post-categories/{$category->id}/edit",
                  '/admin/content', '/admin/content/hero', '/admin/content/services?lang=en', '/admin/content/packages', '/admin/content/faq',
                  '/admin/seo', '/admin/seo/home', '/admin/seo/blog.index', '/admin/profile'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_creating_a_post_generates_latin_slugs(): void
    {
        $this->actingAs($this->editor())->post('/admin/posts', [
            'status' => 'published',
            'title' => ['bg' => 'Защо сайтът ви е бавен', 'en' => ''],
            'body' => ['bg' => 'Текст'],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $post = Post::firstOrFail();
        $this->assertSame('zashto-saytat-vi-e-baven', $post->tr('slug', 'bg'));
        $this->assertNull($post->tr('title', 'en'));
        $this->assertNotNull($post->published_at);
        $this->assertSame(['bg'], $post->locales());
    }

    public function test_post_needs_a_title_in_some_language_and_unique_slug(): void
    {
        $this->actingAs($this->editor());
        $this->post('/admin/posts', ['status' => 'draft', 'title' => ['bg' => '', 'en' => '']])->assertSessionHasErrors('title.bg');

        $this->makePost();
        $this->post('/admin/posts', ['status' => 'draft', 'title' => ['bg' => 'Друго'], 'slug' => ['bg' => 'kak-da-izberete-domeyn']])->assertSessionHasErrors('slug.bg');
        $this->post('/admin/posts', ['status' => 'draft', 'title' => ['bg' => 'Друго'], 'slug' => ['bg' => 'Не Латиница']])->assertSessionHasErrors('slug.bg');
    }

    public function test_cover_image_upload(): void
    {
        $this->actingAs($this->editor())->post('/admin/posts', [
            'status' => 'draft',
            'title' => ['bg' => 'Със снимка'],
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 675),
        ])->assertSessionHasNoErrors();

        $path = Post::firstOrFail()->cover_image;
        $this->assertMatchesRegularExpression('#^uploads/blog/.+\.jpg$#', $path);
        $this->assertFileExists(public_path($path));

        $this->post('/admin/posts', ['status' => 'draft', 'title' => ['bg' => 'SVG'], 'cover_image' => UploadedFile::fake()->create('x.svg', 1, 'image/svg+xml')])
            ->assertSessionHasErrors('cover_image');
    }

    public function test_public_blog_shows_only_visible_posts_per_language(): void
    {
        $this->makePost();
        $this->makePost(['title' => ['bg' => 'Само на български'], 'slug' => ['bg' => 'samo-bg'], 'body' => []]);
        $this->makePost(['title' => ['bg' => 'Чернова'], 'slug' => ['bg' => 'chernova'], 'status' => 'draft']);
        $this->makePost(['title' => ['bg' => 'Насрочена'], 'slug' => ['bg' => 'nasrochena'], 'published_at' => now()->addWeek()]);

        $this->get('/blog')->assertOk()->assertSee('Как да изберете домейн')->assertSee('Само на български')->assertDontSee('Чернова')->assertDontSee('Насрочена');
        $this->get('/en/blog')->assertOk()->assertSee('How to choose a domain')->assertDontSee('Само на български');
        $this->get('/blog/chernova')->assertNotFound();
        $this->get('/en/blog/samo-bg')->assertNotFound();
    }

    public function test_post_page_renders_markdown_safely_with_seo_and_alternates(): void
    {
        $post = $this->makePost([
            'meta_title' => ['bg' => 'Домейн за фирма: кратко ръководство'],
            'meta_description' => ['bg' => 'Как да изберете домейн, без да сгрешите.'],
        ]);

        $this->get('/blog/kak-da-izberete-domeyn')->assertOk()
            ->assertSee('<title>Домейн за фирма: кратко ръководство</title>', false)
            ->assertSee('<meta name="description" content="Как да изберете домейн, без да сгрешите.">', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<h2>Първо</h2>', false)
            ->assertSee('<strong>удебелен</strong>', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('hreflang="en" href="' . url('/en/blog/how-to-choose-a-domain') . '"', false)
            ->assertSee('"@type":"BlogPosting"', false);

        $this->get('/en/blog/how-to-choose-a-domain')->assertOk()->assertSee('<title>How to choose a domain | Creatium Lab</title>', false);
    }

    public function test_category_pages_and_sitemap(): void
    {
        $category = PostCategory::create(['name' => ['bg' => 'SEO съвети', 'en' => 'SEO tips'], 'slug' => ['bg' => 'seo-saveti', 'en' => 'seo-tips']]);
        $this->makePost(['post_category_id' => $category->id]);

        $this->get('/blog/kategoriya/seo-saveti')->assertOk()->assertSee('Как да изберете домейн');
        $this->get('/en/blog/category/seo-tips')->assertOk()->assertSee('How to choose a domain');
        $this->get('/blog/kategoriya/nyama')->assertNotFound();

        $this->get('/sitemap.xml')
            ->assertSee('<loc>' . url('/blog/kak-da-izberete-domeyn') . '</loc>', false)
            ->assertSee('<loc>' . url('/en/blog/category/seo-tips') . '</loc>', false)
            ->assertSee('<loc>' . url('/en/blog') . '</loc>', false);
    }

    public function test_editing_page_content_changes_the_site_and_can_be_reset(): void
    {
        $this->actingAs($this->editor());

        $this->put('/admin/content/hero', ['locale' => 'bg', 'content' => [
            'title' => 'Нов заглавен текст.', 'highlight' => 'Ново.', 'subtitle' => 'Подзаглавие', 'text' => 'Текст',
        ]])->assertRedirect()->assertSessionHasNoErrors();

        $this->get('/')->assertSee('Нов заглавен текст.');
        $this->get('/en')->assertSee('Be recognizable.');

        $this->delete('/admin/content/hero', ['locale' => 'bg']);
        $this->get('/')->assertSee('Бъди разпознаваем.');
    }

    public function test_team_profiles_can_be_edited_in_the_admin(): void
    {
        $this->actingAs($this->editor());

        $this->get('/admin/content/team')->assertOk()->assertSee('Разказ за човека')->assertSee('LinkedIn (адрес на профила)');

        $team = config('creatium.team');
        $team[0]['bio'] = "Първи абзац.\nВтори абзац със буква х.";
        $team[0]['skills'] = "PHP\nLaravel";
        $team[1]['bio'] = implode("\n", $team[1]['bio']);
        $team[1]['skills'] = implode("\n", $team[1]['skills']);

        $this->put('/admin/content/team', ['locale' => 'bg', 'content' => $team])->assertRedirect()->assertSessionHasNoErrors();

        $this->get('/za-nas')->assertSee('Първи абзац.')->assertSee('Втори абзац със буква х.')->assertSee('>PHP<', false)->assertSee('Performance Marketing Expert')
            ->assertSee('images/team/borislav-kostadinov.webp')->assertSee('images/team/vladimir-tsonchev.webp');
    }

    public function test_content_lists_can_add_and_remove_items(): void
    {
        $this->actingAs($this->editor());
        $faq = config('creatium.faq');
        $input = [];
        foreach ($faq as $i => $item) {
            $input[$i] = $item + ($i === 0 ? ['_remove' => '1'] : []);
        }
        $input['new0'] = ['q' => 'Нов въпрос?', 'a' => 'Нов отговор.'];

        $this->put('/admin/content/faq', ['locale' => 'bg', 'content' => $input])->assertSessionHasNoErrors();

        $this->get('/za-nas')->assertSee('Нов въпрос?')->assertDontSee($faq[0]['q']);

        $this->put('/admin/content/packages', ['locale' => 'bg', 'content' => [
            ['name' => 'Старт', 'description' => 'x', 'price_note' => 'от 600 €', 'features' => "Едно\nДве\n\n", 'highlighted' => '0'],
        ]])->assertSessionHasNoErrors();
        $this->get('/paketi')->assertSee('от 600 €');
        $this->assertSame(['Едно', 'Две'], site('packages')[0]['features']);
    }

    public function test_page_seo_overrides(): void
    {
        $this->actingAs($this->editor())->put('/admin/seo/services', ['seo' => [
            'bg' => ['meta_title' => 'Услуги за малки фирми | Creatium Lab', 'canonical_url' => 'https://creatiumlab.com/uslugi', 'og_title' => 'Споделено заглавие'],
            'en' => ['meta_description' => 'English description'],
        ]])->assertSessionHasNoErrors();

        $this->get('/uslugi')
            ->assertSee('<title>Услуги за малки фирми | Creatium Lab</title>', false)
            ->assertSee('<link rel="canonical" href="https://creatiumlab.com/uslugi">', false)
            ->assertSee('<meta property="og:title" content="Споделено заглавие">', false);
        $this->get('/en/services')->assertSee('<meta name="description" content="English description">', false);

        $this->put('/admin/seo/services', ['seo' => ['bg' => ['canonical_url' => 'not a url']]])->assertSessionHasErrors('seo.bg.canonical_url');
    }

    public function test_profile_password_change_needs_current_password(): void
    {
        $user = $this->editor();
        $this->actingAs($user);

        $this->put('/admin/profile', ['name' => 'Борис', 'email' => $user->email, 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'])
            ->assertSessionHasErrors('current_password');
        $this->put('/admin/profile', ['name' => 'Борис', 'email' => $user->email, 'current_password' => 'old-password-123', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertSame('Борис', $user->fresh()->name);
    }

    public function test_editor_image_upload_returns_url(): void
    {
        $this->actingAs($this->editor())
            ->postJson('/admin/uploads/image', ['image' => UploadedFile::fake()->image('a.png')])
            ->assertOk()->assertJsonStructure(['url']);
    }
}
