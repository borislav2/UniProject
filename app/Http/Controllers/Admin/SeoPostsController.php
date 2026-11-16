<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\Seo\PostSeo;

/** „SEO“ → „Статии“: заглавие, описание, автор, ключова дума и оценка на всяка статия. Редактира се в самата статия. */
class SeoPostsController extends Controller
{
    public function index()
    {
        $posts = Post::with(['author', 'category'])->orderByDesc('published_at')->orderByDesc('id')->paginate(30);
        $rows = $posts->getCollection()->map(function (Post $post) {
            $overview = PostSeo::overview($post);
            $l = $overview['locale'];

            return [
                'post' => $post,
                'locale' => $l,
                'title' => PostSeo::effectiveTitle($post, $l),
                'title_own' => (bool) $post->tr('meta_title', $l),
                'description' => PostSeo::effectiveDescription($post, $l),
                'description_own' => (bool) $post->tr('meta_description', $l),
                'keyword' => $post->tr('focus_keyword', $l),
                'author' => PostSeo::authorLabel($post),
                'overview' => $overview,
            ];
        });

        return view('admin.seo.posts', ['posts' => $posts, 'rows' => $rows]);
    }
}
