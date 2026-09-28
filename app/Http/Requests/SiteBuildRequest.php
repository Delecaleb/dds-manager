<?php

namespace App\Http\Requests;

use App\Domain\SiteBuilder\SitePath;
use App\Models\MarketingSite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** The AI Website Builder brief (create and edit). */
class SiteBuildRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route's module:marketing middleware gates access
    }

    public function rules(): array
    {
        $hex = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];

        return [
            'name' => ['required', 'string', 'max:120'],
            'domain' => ['nullable', 'string', 'max:190'],
            'marketing_site_id' => ['nullable', 'integer', Rule::exists('marketing_sites', 'id')],

            'business.type' => ['required', Rule::in(array_keys(config('site_builder.business_types')))],
            'business.summary' => ['required', 'string', 'max:5000'],
            'business.services' => ['nullable', 'string', 'max:3000'],
            'business.street' => ['nullable', 'string', 'max:120'],
            'business.city' => ['nullable', 'string', 'max:80'],
            'business.state' => ['nullable', 'string', 'max:80'],
            'business.postal_code' => ['nullable', 'string', 'max:20'],
            'business.country' => ['nullable', 'string', 'max:80'],
            'business.phone' => ['nullable', 'string', 'max:40'],
            'business.email' => ['nullable', 'email', 'max:190'],
            'business.hours' => ['nullable', 'string', 'max:500'],

            'brand.tone' => ['required', Rule::in(array_keys(config('site_builder.tones')))],
            'brand.tone_notes' => ['nullable', 'string', 'max:1000'],
            'brand.primary_color' => $hex,
            'brand.secondary_color' => $hex,
            'brand.accent_color' => $hex,
            'brand.fonts' => ['nullable', 'string', 'max:200'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],

            'pages' => ['required', 'array', 'min:1', 'max:'.config('site_builder.max_pages')],
            'pages.*.title' => ['required', 'string', 'max:80'],
            'pages.*.path' => ['required', 'string', 'max:150'],
            'pages.*.notes' => ['nullable', 'string', 'max:1000'],

            'seo.keywords' => ['nullable', 'string', 'max:2000'],
            'seo.location' => ['nullable', 'string', 'max:190'],
            'instructions' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'business.summary' => 'business brief',
            'pages.*.title' => 'page title',
            'pages.*.path' => 'page URL',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $seen = [];
            foreach ((array) $this->input('pages', []) as $i => $page) {
                $path = SitePath::normalize((string) ($page['path'] ?? ''));
                if (! SitePath::isValid($path)) {
                    $validator->errors()->add("pages.{$i}.path", 'Use letters, numbers and hyphens, like /services/implants/.');
                } elseif (isset($seen[$path])) {
                    $validator->errors()->add("pages.{$i}.path", "Two pages use {$path}.");
                }
                $seen[$path] = true;
            }
            if ($seen !== [] && ! isset($seen['/'])) {
                $validator->errors()->add('pages', 'Add a home page at /.');
            }
        });
    }

    /** The validated brief, normalized for SiteBuild. */
    public function brief(): array
    {
        $data = $this->validated();

        return [
            'name' => $data['name'],
            'domain' => filled($data['domain'] ?? null) ? MarketingSite::normalizeDomain($data['domain']) : null,
            'marketing_site_id' => $data['marketing_site_id'] ?? null,
            'business' => array_map(fn ($v) => is_string($v) ? trim($v) : $v, $data['business']),
            'brand' => $data['brand'],
            'pages' => array_values(array_map(fn (array $page) => [
                'title' => trim($page['title']),
                'path' => SitePath::normalize($page['path']),
                'notes' => trim((string) ($page['notes'] ?? '')),
            ], $data['pages'])),
            'seo' => [
                'keywords' => array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', (string) ($data['seo']['keywords'] ?? '')) ?: []))),
                'location' => $data['seo']['location'] ?? null,
            ],
            'instructions' => $data['instructions'] ?? null,
        ];
    }
}
