<?php

namespace App\Domain\SiteBuilder;

/**
 * Writes a site's content. The jobs depend on this contract only; Claude is the
 * implementation (ClaudeSiteContentGenerator), tests bind a fake.
 *
 * Every result carries `usage` = {input, output, cache_read} token counts.
 */
interface SiteContentGenerator
{
    /**
     * The design system shared by every page.
     *
     * @param  array  $brief  SiteBuild::brief()
     * @param  array{data: string, media_type: string}|null  $logo  raster logo (base64) for the model to see
     * @return array{css: string, header_html: string, footer_html: string, google_fonts_url: string, usage: array}
     */
    public function design(array $brief, ?array $logo): array;

    /**
     * One page's <main> content and SEO meta.
     *
     * @param  array{title: string, path: string, notes?: string}  $page
     * @param  string|null  $revisionNotes  extra instructions for a regeneration
     * @return array{meta_title: string, meta_description: string, body_html: string, usage: array}
     */
    public function page(array $brief, array $design, array $page, ?string $revisionNotes): array;
}
