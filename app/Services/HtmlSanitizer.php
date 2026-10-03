<?php

namespace App\Services;

/**
 * HTML sanitizer for staff-authored rich text rendered with {!! !!}.
 * DOM-based allowlist: parses with DOMDocument (no regex tag parsing),
 * drops disallowed elements (keeping inner text), strips event handlers,
 * style attributes and javascript:/data:/vbscript: URLs, and allowlists
 * safe attributes per tag. Preserves legitimate formatting.
 */
class HtmlSanitizer
{
    private const ALLOWED = [
        'p' => [], 'br' => [], 'b' => [], 'strong' => [], 'i' => [], 'em' => [],
        'u' => [], 'ul' => [], 'ol' => [], 'li' => [], 'h1' => [], 'h2' => [],
        'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [], 'blockquote' => [],
        'code' => [], 'pre' => [], 'hr' => [], 'table' => [], 'thead' => [],
        'tbody' => [], 'tr' => [], 'th' => [], 'td' => [], 'span' => [],
        'div' => [], 'a' => ['href', 'title'], 'img' => ['src', 'alt', 'title'],
    ];

    public static function clean(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        $prev = libxml_use_internal_errors(true);
        $doc = new \DOMDocument('1.0', 'UTF-8');
        // Wrap fragment so loadHTML always has a body to extract from.
        $doc->loadHTML('<?xml encoding="utf-8" ?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $wrapper = $doc->getElementsByTagName('div')->item(0);
        if (! $wrapper) {
            return '';
        }

        self::walk($wrapper);

        $out = '';
        foreach ($wrapper->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    private static function walk(\DOMNode $node): void
    {
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);
            if ($child instanceof \DOMElement) {
                $tag = strtolower($child->tagName);
                if (! array_key_exists($tag, self::ALLOWED)) {
                    // Drop dangerous nodes entirely (incl. children for script/style/iframe/etc).
                    if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'link', 'meta', 'form', 'input', 'button', 'video', 'audio', 'source', 'svg', 'math'], true)) {
                        $node->removeChild($child);
                    } else {
                        // Unknown formatting tag: unwrap (keep inner text/children).
                        self::walk($child);
                        while ($child->firstChild) {
                            $node->insertBefore($child->firstChild, $child);
                        }
                        $node->removeChild($child);
                    }

                    continue;
                }
                // Strip disallowed / dangerous attributes.
                $allowedAttrs = self::ALLOWED[$tag];
                foreach (iterator_to_array($child->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    if (str_starts_with($name, 'on') || $name === 'style' || $name === 'srcset') {
                        $child->removeAttribute($attr->name);

                        continue;
                    }
                    if (! in_array($name, $allowedAttrs, true)) {
                        $child->removeAttribute($attr->name);

                        continue;
                    }
                    // Neutralize dangerous URL schemes in href/src.
                    $val = trim($child->getAttribute($attr->name));
                    if (preg_match('#^\s*(javascript|data\s*:\s*text/html|vbscript|file)\s*:#i', $val)) {
                        $child->setAttribute($attr->name, '#blocked');
                    } elseif ($tag === 'img' && $name === 'src' && ! preg_match('#^(/|https?://)#i', $val)) {
                        $child->removeAttribute('src');
                    } elseif ($tag === 'a' && $name === 'href' && ! preg_match('#^(/|https?://|mailto:|tel:|#)#i', $val)) {
                        $child->setAttribute('href', '#blocked');
                    }
                }
                // Force safe link behaviour.
                if ($tag === 'a') {
                    $child->setAttribute('rel', 'noopener noreferrer nofollow');
                }
                self::walk($child);
            } elseif ($child instanceof \DOMComment || $child instanceof \DOMProcessingInstruction) {
                $node->removeChild($child);
            }
        }
    }
}
