<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Support\Content;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Edits the site texts from config/creatium*.php. The form is built from the structure of the Bulgarian
 * default, so it keeps working when a field is added to the config file.
 */
class ContentController extends Controller
{
    public function __construct(private Content $content)
    {
    }

    public function index()
    {
        $edited = ContentBlock::where('key', 'not like', 'seo:%')->get()->groupBy('key');

        return view('admin.content.index', ['blocks' => Content::BLOCKS, 'edited' => $edited]);
    }

    public function edit(Request $request, string $key)
    {
        abort_unless(isset(Content::BLOCKS[$key]), 404);
        $locale = $request->query('lang') === 'en' ? 'en' : 'bg';

        return view('admin.content.edit', [
            'key' => $key,
            'label' => Content::BLOCKS[$key],
            'locale' => $locale,
            'template' => config("creatium.$key"),
            'value' => $this->content->get($key, $locale),
            'block' => ContentBlock::where(['key' => $key, 'locale' => $locale])->with('editor')->first(),
        ]);
    }

    public function update(Request $request, string $key)
    {
        abort_unless(isset(Content::BLOCKS[$key]), 404);
        $locale = $request->validate(['locale' => ['required', Rule::in(['bg', 'en'])]])['locale'];

        $value = $this->normalize(config("creatium.$key"), $request->input("content"), $key, $request);
        $this->content->save($key, $locale, $value, $request->user()->id);

        return redirect()->route('admin.content.edit', ['key' => $key, 'lang' => $locale])->with('success', 'Текстът е запазен и вече се вижда на сайта.');
    }

    public function reset(Request $request, string $key)
    {
        abort_unless(isset(Content::BLOCKS[$key]), 404);
        $locale = $request->input('locale') === 'en' ? 'en' : 'bg';
        $this->content->reset($key, $locale);

        return redirect()->route('admin.content.edit', ['key' => $key, 'lang' => $locale])->with('success', 'Върнат е текстът по подразбиране.');
    }

    /** Shapes the submitted form like the config default: lists of lines, item lists, yes/no fields, images. */
    private function normalize(mixed $template, mixed $input, string $path, Request $request): mixed
    {
        if (is_bool($template)) {
            return filter_var($input, FILTER_VALIDATE_BOOLEAN);
        }

        if (is_array($template) && array_is_list($template)) {
            if ($template === [] || ! is_array($template[0])) {
                return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $input)), 'strlen'));
            }

            $items = [];
            foreach ((array) $input as $index => $item) {
                if (! is_array($item) || ! empty($item['_remove'])) {
                    continue;
                }
                $normalized = $this->normalize($template[0], $item, "$path.$index", $request);
                if (array_filter($normalized, fn ($v) => filled($v) && $v !== false)) {
                    $items[] = $normalized;
                }
            }

            return $items;
        }

        if (is_array($template)) {
            $out = [];
            foreach ($template as $field => $fieldTemplate) {
                $out[$field] = $this->normalize($fieldTemplate, is_array($input) ? ($input[$field] ?? null) : null, "$path.$field", $request);
            }

            return $out;
        }

        if (str_ends_with($path, 'image')) {
            return $this->image($path, $input, $request);
        }

        $value = trim((string) $input);
        if (mb_strlen($value) > 5000) {
            throw \Illuminate\Validation\ValidationException::withMessages(['content' => 'Някое от полетата е твърде дълго (над 5000 знака).']);
        }

        return $value === '' ? null : $value;
    }

    private function image(string $path, mixed $current, Request $request): ?string
    {
        $current = Uploads::isUpload($current) ? $current : null;
        $file = $request->file("files.$path");

        if ($file) {
            Validator::make(['image' => $file], ['image' => Uploads::IMAGE_RULE])->validate();

            return Uploads::storeImage($file, 'content');
        }

        return $request->boolean("remove_files.$path") ? null : $current;
    }
}
