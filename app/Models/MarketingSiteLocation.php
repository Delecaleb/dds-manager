<?php

namespace App\Models;

use App\Models\Concerns\HasMarketingLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * One location a tracked website serves, with the two ways a visit on a shared website is
 * recognised as this location's: the value the signup form sends, and the URL paths that
 * belong to it. VisitLocationResolver is the only reader of both.
 */
class MarketingSiteLocation extends Model
{
    use HasMarketingLocation;

    protected $fillable = ['site_id', 'office_id', 'clinic_num', 'code', 'path_prefixes'];

    protected function casts(): array
    {
        return [
            'path_prefixes' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(MarketingSite::class, 'site_id');
    }

    /** The value a signup form sends to name this location: the admin's code, else the name as a slug. */
    public function formCode(string $locationName): string
    {
        return self::normalizeCode($this->code ?? '') ?: self::normalizeCode($locationName);
    }

    /** "Grand Rapids", "grand_rapids" and " GRAND-RAPIDS " all compare as "grand-rapids". */
    public static function normalizeCode(string $value): string
    {
        return Str::slug($value);
    }

    /**
     * Clean a typed list of paths ("/grand-rapids, https://site.com/ann-arbor/") into
     * lowercase, leading-slash prefixes without a trailing slash.
     *
     * @return list<string>
     */
    public static function normalizePaths(string $input): array
    {
        $paths = [];
        foreach (preg_split('/[\s,]+/', strtolower($input)) ?: [] as $path) {
            if ($path === '') {
                continue;
            }

            // A pasted full URL ("site.com/ann-arbor", with or without https://) keeps its path.
            if (! str_starts_with($path, '/') && ! str_contains($path, '://') && str_contains(explode('/', $path)[0], '.')) {
                $path = 'https://'.$path;
            }
            $path = (string) (parse_url($path, PHP_URL_PATH) ?? '');
            $path = '/'.trim($path, '/');

            if ($path !== '/') {
                $paths[] = mb_substr($path, 0, 255);
            }
        }

        return array_values(array_unique($paths));
    }

    /** True when the page path is one of this location's paths or sits under one. */
    public function coversPath(?string $path): bool
    {
        $path = '/'.trim(strtolower((string) $path), '/');

        foreach ($this->path_prefixes ?? [] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return true;
            }
        }

        return false;
    }
}
