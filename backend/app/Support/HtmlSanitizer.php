<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Str;

class HtmlSanitizer
{
    private static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $clean = trim(self::purifier()->purify($html));

        return $clean === '' || $clean === '<p></p>' ? null : $clean;
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier) {
            return self::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $cacheDirectory = storage_path('framework/cache/htmlpurifier');
        if (! is_dir($cacheDirectory)) {
            @mkdir($cacheDirectory, 0755, true);
        }
        $config->set('Cache.SerializerPath', $cacheDirectory);
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', implode(',', [
            'h2', 'h3', 'h4', 'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'blockquote',
            'ul', 'ol', 'li', 'a[href|title|target]', 'hr', 'code', 'pre',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'iframe[src|width|height|title]',
            'img[src|alt|width|height]',
        ]));
        $config->set('HTML.TargetBlank', false);
        $config->set('HTML.Nofollow', false);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^https://(www\.youtube-nocookie\.com/embed/|www\.youtube\.com/embed/|player\.vimeo\.com/video/)%');
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('URI.Host', parse_url((string) config('app.url'), PHP_URL_HOST) ?: null);
        $config->set('URI.DisableExternalResources', true);

        return self::$purifier = new HTMLPurifier($config);
    }

    public static function withHeadingAnchors(?string $html): array
    {
        if (! $html || ! str_contains($html, '<h2')) {
            return ['html' => $html, 'toc' => []];
        }

        $toc = [];
        $used = [];
        $html = preg_replace_callback('#<h2(\s[^>]*)?>(.*?)</h2>#si', function (array $m) use (&$toc, &$used): string {
            $text = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5));
            $id = Str::slug($text) ?: 'section';
            $base = $id;
            $n = 2;
            while (isset($used[$id])) {
                $id = $base.'-'.$n++;
            }
            $used[$id] = true;
            $toc[] = ['id' => $id, 'text' => $text];

            return '<h2 id="'.$id.'">'.$m[2].'</h2>';
        }, $html);

        return ['html' => $html, 'toc' => $toc];
    }

    public static function readingMinutes(?string $html): int
    {
        $words = str_word_count(strip_tags((string) $html));

        return max(1, (int) ceil($words / 220));
    }
}
