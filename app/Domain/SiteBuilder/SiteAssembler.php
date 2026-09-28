<?php

namespace App\Domain\SiteBuilder;

use App\Models\MarketingSite;
use App\Models\SiteBuildPage;
use App\Models\SiteBuildVersion;
use Illuminate\Support\Facades\Storage;

/**
 * Turns a generated version into site files. Everything that doesn't need judgment is
 * written here, not by the model: the document shell and SEO head, LocalBusiness
 * structured data, relative links, sitemap.xml, robots.txt and the tracking snippet.
 *
 * Two modes: export (the downloadable site — clean directory URLs, tracking snippet) and
 * preview (served inside the app — links point at index.html files, no tracking, so
 * previews never count as visits).
 */
final class SiteAssembler
{
    public const STYLESHEET = 'assets/styles.css';

    /** @return array<string, string> site file path => contents */
    public function files(SiteBuildVersion $version, bool $preview = false): array
    {
        $brief = $version->brief;
        $files = [self::STYLESHEET => (string) ($version->design['css'] ?? '')];

        if ($logo = $this->logoFile($brief)) {
            $files[$logo['file']] = $logo['contents'];
        }

        foreach ($version->pages as $page) {
            $file = SitePath::file($page->path);
            $files[$file] = $this->document($version, $page, $file, $preview);
        }

        if (! $preview) {
            $files['robots.txt'] = $this->robots($brief);
            if ($sitemap = $this->sitemap($version)) {
                $files['sitemap.xml'] = $sitemap;
            }
        }

        return $files;
    }

    /** One file of the preview, or null when the version has no such file. */
    public function previewFile(SiteBuildVersion $version, string $file): ?string
    {
        return $this->files($version, preview: true)[$file] ?? null;
    }

    /** The logo as a site file ("assets/logo.png"), or null without one. */
    public function logoFile(array $brief): ?array
    {
        $path = $brief['logo_path'] ?? null;
        if (! $path) {
            return null;
        }
        if (array_key_exists($path, $this->logos)) {
            return $this->logos[$path];
        }

        $disk = Storage::disk(config('site_builder.disk'));

        return $this->logos[$path] = $disk->exists($path) ? [
            'file' => self::logoFileName($path),
            'contents' => $disk->get($path),
        ] : null;
    }

    /** "site-builder/logos/abc.PNG" → "assets/logo.png" (the URL the model is told to use). */
    public static function logoFileName(string $storedPath): string
    {
        return 'assets/logo.'.strtolower(pathinfo($storedPath, PATHINFO_EXTENSION));
    }

    /** @var array<string, array{file: string, contents: string}|null> logo reads, per stored path */
    private array $logos = [];

    private function document(SiteBuildVersion $version, SiteBuildPage $page, string $file, bool $preview): string
    {
        $brief = $version->brief;
        $design = $version->design ?? [];
        $meta = $page->meta ?? [];
        $name = $brief['name'];
        $title = $meta['meta_title'] ?? "{$page->title} | {$name}";
        $description = $meta['meta_description'] ?? '';
        $url = $this->absoluteUrl($brief, $page->path);
        $logo = $this->logoFile($brief);

        $body = $page->hasContent()
            ? $page->body_html
            : '<section><h1>'.e($page->title).'</h1><p>This page is being updated. Please check back soon.</p></section>';

        $head = [
            '<meta charset="utf-8">',
            '<meta name="viewport" content="width=device-width, initial-scale=1">',
            '<title>'.e($title).'</title>',
            $description !== '' ? '<meta name="description" content="'.e($description).'">' : null,
            $url ? '<link rel="canonical" href="'.e($url).'">' : null,
            '<meta property="og:type" content="website">',
            '<meta property="og:site_name" content="'.e($name).'">',
            '<meta property="og:title" content="'.e($title).'">',
            $description !== '' ? '<meta property="og:description" content="'.e($description).'">' : null,
            $url ? '<meta property="og:url" content="'.e($url).'">' : null,
            $logo ? '<link rel="icon" href="/'.$logo['file'].'">' : null,
        ];

        $fonts = (string) ($design['google_fonts_url'] ?? '');
        if (str_starts_with($fonts, 'https://fonts.googleapis.com/')) {
            $head[] = '<link rel="preconnect" href="https://fonts.googleapis.com">';
            $head[] = '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
            $head[] = '<link rel="stylesheet" href="'.e($fonts).'">';
        }
        $head[] = '<link rel="stylesheet" href="/'.self::STYLESHEET.'">';
        $head[] = '<script type="application/ld+json">'.json_encode($this->structuredData($brief, $url), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG).'</script>';

        if (! $preview && ! empty($brief['tracking_site_key'])) {
            $head[] = MarketingSite::snippetFor($brief['tracking_site_key'], route('tracking.script'));
        }

        $html = "<!doctype html>\n<html lang=\"en\">\n<head>\n"
            .implode("\n", array_filter($head))
            ."\n</head>\n<body>\n"
            .SiteHtml::markCurrent((string) ($design['header_html'] ?? ''), $page->path)
            ."\n<main id=\"main\">\n".$body."\n</main>\n"
            .($design['footer_html'] ?? '')
            ."\n</body>\n</html>\n";

        return SiteHtml::relativize($html, $file, explicitIndex: $preview);
    }

    /** schema.org LocalBusiness (or subtype) for the business, from the brief only. */
    private function structuredData(array $brief, ?string $url): array
    {
        $business = $brief['business'] ?? [];
        $address = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $business['street'] ?? null,
            'addressLocality' => $business['city'] ?? null,
            'addressRegion' => $business['state'] ?? null,
            'postalCode' => $business['postal_code'] ?? null,
            'addressCountry' => $business['country'] ?? null,
        ]);

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => $business['type'] ?? 'LocalBusiness',
            'name' => $brief['name'],
            'description' => $business['summary'] ?? null,
            'url' => $url ? $this->absoluteUrl($brief, '/') : null,
            'telephone' => $business['phone'] ?? null,
            'email' => $business['email'] ?? null,
            'address' => count($address) > 1 ? $address : null,
            'openingHours' => $business['hours'] ?? null,
        ]);
    }

    private function absoluteUrl(array $brief, string $path): ?string
    {
        $domain = $brief['domain'] ?? null;

        return $domain ? 'https://'.$domain.$path : null;
    }

    private function robots(array $brief): string
    {
        $robots = "User-agent: *\nAllow: /\n";
        if ($sitemap = $this->absoluteUrl($brief, '/sitemap.xml')) {
            $robots .= "\nSitemap: {$sitemap}\n";
        }

        return $robots;
    }

    private function sitemap(SiteBuildVersion $version): ?string
    {
        if (empty($version->brief['domain'])) {
            return null;
        }

        $urls = $version->pages->map(fn (SiteBuildPage $page) => '  <url><loc>'.e($this->absoluteUrl($version->brief, $page->path)).'</loc></url>')->implode("\n");

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n{$urls}\n</urlset>\n";
    }
}
