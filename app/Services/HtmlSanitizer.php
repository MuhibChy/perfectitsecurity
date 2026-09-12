<?php

namespace App\Services;

/**
 * Minimal HTML sanitizer for staff-authored rich text rendered with {!! !!}.
 * Allowlist-based: strips scripts, iframes, objects, event handlers and javascript: URLs.
 * For full WYSIWYG needs, replace with HTML Purifier (e.g. mews/purifier).
 */
class HtmlSanitizer
{
    public static function clean(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        // Remove script/style/iframe/object/embed/link tags with content.
        $html = preg_replace('#<(script|style|iframe|object|embed|link|meta)[^>]*?>.*?</\1\s*>#is', '', $html);
        // Remove self-closing risky tags.
        $html = preg_replace('#<(script|iframe|object|embed|link|meta)[^>]*?/?>#i', '', $html);
        // Remove event handler attributes (onclick, onload, ...).
        $html = preg_replace('#\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
        // Neutralize javascript:/data:text/html/vbscript: URLs.
        $html = preg_replace('#(href|src|xlink:href)\s*=\s*([\'"]?)\s*(javascript|data:text/html|vbscript)\s*:#i', '$1=$2blocked:', $html);

        // Allowlist of safe tags; strip everything else (keeps inner text).
        $allowed = '<p><br><b><strong><i><em><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><code><pre><hr><table><thead><tbody><tr><th><td><img><span><div>';
        $html = strip_tags($html, $allowed);

        return $html;
    }
}
