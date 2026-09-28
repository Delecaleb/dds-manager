<?php

namespace App\Domain\SiteBuilder;

/**
 * A generated site's page URLs. Paths are stored in one canonical form — lowercase,
 * leading and trailing slash ("/", "/about/", "/services/implants/") — and each page is
 * written as a directory index file, so the URLs stay clean on any static host.
 */
final class SitePath
{
    /** Allowed characters once normalized: lowercase letters, digits, hyphens, slashes. */
    public const PATTERN = '#^/([a-z0-9-]+/)*$#';

    public static function normalize(string $path): string
    {
        $path = strtolower(trim($path));
        $path = preg_replace('#[^a-z0-9/-]+#', '-', $path) ?? $path;
        $path = preg_replace('#/+#', '/', '/'.trim($path, '/').'/') ?? $path;

        return $path === '//' ? '/' : $path;
    }

    public static function isValid(string $path): bool
    {
        return (bool) preg_match(self::PATTERN, $path);
    }

    /** "/" → "index.html", "/about/" → "about/index.html". */
    public static function file(string $path): string
    {
        $trimmed = trim($path, '/');

        return $trimmed === '' ? 'index.html' : $trimmed.'/index.html';
    }

    /** How many directories deep a site file sits ("about/index.html" → 1). */
    public static function depth(string $file): int
    {
        return substr_count($file, '/');
    }

    /**
     * Rewrite a root-relative URL ("/about/", "/assets/styles.css") so it works from the
     * given file, without a web server: relative, and in preview mode pointing at the
     * index.html file itself rather than the directory.
     */
    public static function relative(string $url, string $fromFile, bool $explicitIndex): string
    {
        [$target, $suffix] = self::splitSuffix($url);
        $target = ltrim($target, '/');
        if ($explicitIndex && ($target === '' || str_ends_with($target, '/'))) {
            $target .= 'index.html';
        }

        $up = str_repeat('../', self::depth($fromFile));
        $relative = $up.$target;

        return ($relative === '' ? './' : $relative).$suffix;
    }

    /** @return array{0: string, 1: string} path, then any "?query" / "#fragment" */
    private static function splitSuffix(string $url): array
    {
        $cut = strcspn($url, '?#');

        return [substr($url, 0, $cut), substr($url, $cut)];
    }
}
