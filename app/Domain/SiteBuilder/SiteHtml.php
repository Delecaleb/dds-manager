<?php

namespace App\Domain\SiteBuilder;

/**
 * Cleans model-written HTML fragments before they become site files. The sites are
 * meant to be script-free (the prompt says so); this enforces it. It is a cleanup pass,
 * not the security boundary — previews are also served under a script-src 'none' CSP.
 */
final class SiteHtml
{
    public static function clean(string $html): string
    {
        // Elements that run code, load other documents, or rewrite the page's base URL.
        $html = preg_replace('#<(script|iframe|object|embed|frame|frameset|applet|base|meta|link)\b[^>]*>.*?</\1\s*>#is', '', $html) ?? '';
        $html = preg_replace('#<(script|iframe|object|embed|frame|frameset|applet|base|meta|link)\b[^>]*/?>#i', '', $html) ?? '';

        // Inline event handlers (onclick=…, onload=…).
        $html = preg_replace('#\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? '';

        // javascript: / vbscript: / data:text URLs in link-like attributes.
        return preg_replace('#\b(href|src|action|formaction|xlink:href)\s*=\s*(["\']?)\s*(javascript|vbscript|data:text)[^"\'>\s]*\2#i', '$1="#"', $html) ?? '';
    }

    /**
     * Rewrite every root-relative href/src ("/about/", "/assets/x.css") in a document so
     * it resolves from $fromFile without a web server (see SitePath::relative).
     */
    public static function relativize(string $html, string $fromFile, bool $explicitIndex): string
    {
        return preg_replace_callback(
            '#\b(href|src)=(["\'])(/(?!/)[^"\']*)\2#i',
            fn (array $m) => $m[1].'='.$m[2].SitePath::relative($m[3], $fromFile, $explicitIndex).$m[2],
            $html,
        ) ?? $html;
    }

    /** Mark the nav link to the current page. */
    public static function markCurrent(string $headerHtml, string $path): string
    {
        $quoted = preg_quote($path, '#');

        return preg_replace('#(<a\b[^>]*\bhref=(["\'])'.$quoted.'\2)(?![^>]*aria-current)#i', '$1 aria-current="page"', $headerHtml) ?? $headerHtml;
    }
}
