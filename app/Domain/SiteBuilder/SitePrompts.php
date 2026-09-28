<?php

namespace App\Domain\SiteBuilder;

/**
 * Prompt text and output schemas for site generation, kept apart from the API plumbing
 * so they can be tuned without touching it.
 *
 * Cache layout (prefix order): the static system text never changes; the brief (and,
 * for pages, the design system) is fixed for a version — both carry a cache breakpoint,
 * so every page call of a run re-reads them from cache and only the page request varies.
 */
final class SitePrompts
{
    public function system(): string
    {
        return <<<'TXT'
        You build marketing websites for local businesses, mostly dental practices. Your output is static HTML and CSS that we assemble into site files.

        How the site is assembled:
        - We write each page's document shell ourselves: <html>, <head> (title, meta description, canonical URL, Open Graph, structured data, fonts, stylesheet link) and <body>. You never write those.
        - Every page is your shared header, then that page's <main> content, then your shared footer, all styled by one stylesheet served at /assets/styles.css.
        - Write internal links and asset references root-relative ("/about/", "/assets/logo.png"); we convert them to relative links. Link only to pages in the site's page list, or to tel:, mailto: and https:// URLs.
        - No JavaScript: no <script> elements, no inline event handlers. Interactive pieces such as the mobile menu or FAQ answers use CSS-only patterns (<details>, checkbox toggles). Scripts are stripped.
        - There are no photos available. Use the logo when one is provided, CSS shapes and gradients, and inline SVG icons. Never reference an image file that doesn't exist or an external image URL.

        Quality bar: mobile-first and responsive; accessible (semantic landmarks, one h1 per page, meaningful alt text, WCAG AA contrast, visible focus styles); fast (no frameworks). Copy is specific to this business, written in the requested brand tone, and uses the SEO keywords naturally in headings and body text without stuffing. Never invent facts the brief does not support — no made-up awards, years in business, staff names, prices, reviews or statistics. When a detail is unknown, write around it.
        TXT;
    }

    /** The per-site brief the model sees. Internal fields (storage paths, keys) are left out. */
    public function brief(array $brief, ?string $logoUrl): string
    {
        $public = [
            'business_name' => $brief['name'],
            'website_domain' => $brief['domain'] ?: null,
            'business' => $brief['business'],
            'brand' => $brief['brand'],
            'pages' => array_map(fn (array $p) => array_filter([
                'title' => $p['title'],
                'url' => $p['path'],
                'notes' => $p['notes'] ?? null,
            ]), $brief['pages']),
            'seo' => $brief['seo'],
            'additional_instructions' => $brief['instructions'] ?: null,
        ];

        $logo = $logoUrl
            ? "The business logo is at {$logoUrl}; use it in the header."
            : 'There is no logo: set the business name as a styled wordmark in the header.';

        return "Site brief:\n".json_encode($public, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ."\n\n".$logo;
    }

    public function designRequest(): string
    {
        return <<<'TXT'
        Create the design system for this site.

        - css: the complete stylesheet. Build the palette from the brand colors and pick type that fits the brand tone. Define reusable classes for page sections (for example hero, feature grid, cards, call-to-action band, contact block, FAQ, two-column text) so each page can be written with these classes alone. Start the stylesheet with a comment listing every section class and its purpose — page authors read it to know what exists.
        - header_html: a <header> element with the logo or wordmark (linking to "/") and navigation to every page in the page list, in order, with a CSS-only mobile menu. Mark nothing as the current page; we add aria-current per page.
        - footer_html: a <footer> element with the business name, and the address, phone, email and hours exactly as given in the brief (omit any that are missing), plus links to the pages.
        - google_fonts_url: one https://fonts.googleapis.com/css2?... URL for the web fonts the stylesheet uses, or an empty string to use system fonts.
        TXT;
    }

    public function designBlock(array $design): string
    {
        return "Design system for this site (use these classes; do not restate the stylesheet):\n\n"
            ."/assets/styles.css:\n".$design['css']
            ."\n\nShared header (already on every page):\n".$design['header_html']
            ."\n\nShared footer (already on every page):\n".$design['footer_html'];
    }

    public function pageRequest(array $page, ?string $revisionNotes): string
    {
        $text = "Write the page \"{$page['title']}\" at {$page['path']}.";
        if (! empty($page['notes'])) {
            $text .= "\n\nWhat this page should cover: {$page['notes']}";
        }
        if ($revisionNotes) {
            $text .= "\n\nThis is a revision of an earlier version. Changes requested: {$revisionNotes}";
        }

        return $text."\n\n"
            ."- meta_title: the <title>, at most 60 characters, leading with the page's main keyword.\n"
            ."- meta_description: at most 155 characters, a reason to click.\n"
            ."- body_html: only the contents of <main> — sections built from the stylesheet's classes, exactly one <h1>, and a clear call to action (phone or contact page).";
    }

    /** @return array<string, mixed> JSON schema for the design call */
    public function designSchema(): array
    {
        return $this->schema([
            'css' => 'string',
            'header_html' => 'string',
            'footer_html' => 'string',
            'google_fonts_url' => 'string',
        ]);
    }

    /** @return array<string, mixed> JSON schema for a page call */
    public function pageSchema(): array
    {
        return $this->schema([
            'meta_title' => 'string',
            'meta_description' => 'string',
            'body_html' => 'string',
        ]);
    }

    private function schema(array $fields): array
    {
        return [
            'type' => 'object',
            'properties' => array_map(fn (string $type) => ['type' => $type], $fields),
            'required' => array_keys($fields),
            'additionalProperties' => false,
        ];
    }
}
