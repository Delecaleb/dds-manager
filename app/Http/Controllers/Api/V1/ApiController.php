<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Support\MetricFilter;
use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthenticateApiKey;
use App\Models\ApiKey;
use Illuminate\Http\Request;

/** Shared by Developer API v1 controllers: the calling key and the location it is bound to. */
abstract class ApiController extends Controller
{
    protected function apiKey(Request $request): ApiKey
    {
        return $request->attributes->get(AuthenticateApiKey::ATTRIBUTE);
    }

    /** The key's location as a domain filter: its office, and its clinic when it has one. */
    protected function filterFor(ApiKey $key, string $start, string $end): MetricFilter
    {
        return new MetricFilter(
            $start,
            $end,
            $key->clinic_num !== null ? [(int) $key->clinic_num] : [],
            [],
            null,
            (int) $key->office_id,
        );
    }

    protected function recordRows(Request $request, int $rows): void
    {
        $request->attributes->set(AuthenticateApiKey::ROWS_ATTRIBUTE, $rows);
    }
}
