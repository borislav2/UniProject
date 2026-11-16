<?php

namespace App\Support\Seo;

use DOMDocument;
use DOMXPath;

/** Какво казва HTML страницата за търсачките: заглавие, описание, canonical, роботи, заглавия, връзки, картинки, JSON-LD. */
class PageMeta
{
    public static function parse(string $html, string $baseUrl = ''): array
    {
        $out = [
            'title' => null, 'description' => null, 'canonical' => null, 'robots' => null, 'noindex' => false,
            'h1' => [], 'headings' => [], 'hreflang' => [], 'links' => [], 'images' => ['total' => 0, 'no_alt' => 0, 'no_size' => 0, 'examples' => []],
            'jsonld' => ['blocks' => 0, 'invalid' => 0, 'errors' => []], 'words' => 0, 'lang' => null,
        ];
        if (trim($html) === '') {
            return $out;
        }

        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xp = new DOMXPath($doc);

        $title = $xp->query('//head/title')->item(0);
        $out['title'] = $title ? self::clean($title->textContent) : null;
        $out['lang'] = $doc->documentElement?->getAttribute('lang') ?: null;

        foreach ($xp->query('//head/meta[@name]') as $meta) {
            $name = strtolower($meta->getAttribute('name'));
            if ($name === 'description') {
                $out['description'] = self::clean($meta->getAttribute('content'));
            } elseif ($name === 'robots') {
                $out['robots'] = strtolower($meta->getAttribute('content'));
                $out['noindex'] = str_contains($out['robots'], 'noindex');
            }
        }
        foreach ($xp->query('//head/link[@rel]') as $link) {
            $rel = strtolower($link->getAttribute('rel'));
            if ($rel === 'canonical') {
                $out['canonical'] = trim($link->getAttribute('href'));
            } elseif ($rel === 'alternate' && $link->getAttribute('hreflang') !== '') {
                $out['hreflang'][strtolower($link->getAttribute('hreflang'))] = trim($link->getAttribute('href'));
            }
        }

        foreach ($xp->query('//h1|//h2|//h3|//h4|//h5|//h6') as $h) {
            $level = (int) substr($h->nodeName, 1);
            $text = self::clean($h->textContent);
            $out['headings'][] = $level;
            if ($level === 1) {
                $out['h1'][] = $text;
            }
        }

        foreach ($xp->query('//a[@href]') as $a) {
            $href = trim($a->getAttribute('href'));
            if ($href === '' || preg_match('/^(#|mailto:|tel:|javascript:|sms:|data:)/i', $href)) {
                continue;
            }
            $out['links'][] = $href;
        }

        foreach ($xp->query('//body//img') as $img) {
            $out['images']['total']++;
            $missingAlt = ! $img->hasAttribute('alt');
            $missingSize = ! $img->getAttribute('width') || ! $img->getAttribute('height');
            $out['images']['no_alt'] += $missingAlt ? 1 : 0;
            $out['images']['no_size'] += $missingSize ? 1 : 0;
            if ($missingAlt && count($out['images']['examples']) < 3) {
                $out['images']['examples'][] = basename(parse_url($img->getAttribute('src'), PHP_URL_PATH) ?: $img->getAttribute('src'));
            }
        }

        foreach ($xp->query('//script[@type="application/ld+json"]') as $script) {
            $out['jsonld']['blocks']++;
            $data = json_decode($script->textContent, true);
            $problem = null;
            if (! is_array($data)) {
                $problem = 'не е валиден JSON';
            } else {
                $items = array_is_list($data) ? $data : [$data];
                foreach ($items as $item) {
                    if (! is_array($item) || (! isset($item['@context']) && ! isset($item['@graph']))) {
                        $problem = 'няма @context';
                    }
                }
            }
            if ($problem) {
                $out['jsonld']['invalid']++;
                $out['jsonld']['errors'][] = $problem;
            }
        }

        foreach ($xp->query('//script|//style|//noscript|//template') as $node) {
            $node->parentNode?->removeChild($node);
        }
        $root = $xp->query('//main')->item(0) ?? $xp->query('//body')->item(0);
        $text = $root ? self::clean($root->textContent) : '';
        $out['words'] = $text === '' ? 0 : count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY));

        return $out;
    }

    /** Нивата на заглавията, при които се прескача ниво (h1 право към h3) или първото не е h1. */
    public static function headingSkips(array $levels): int
    {
        $skips = 0;
        $prev = 0;
        foreach ($levels as $level) {
            if ($level > $prev + 1) {
                $skips++;
            }
            $prev = $level;
        }

        return $skips;
    }

    public static function clean(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}
