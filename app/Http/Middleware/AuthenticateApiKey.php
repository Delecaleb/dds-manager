<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Developer API authentication: a Bearer key, active, holding the route's ability. The key
 * (and so the one location it may read) is put on the request as the `api_key` attribute.
 *
 * Every request made with a valid key is written to api_request_logs once the response has
 * been sent — including refusals such as 403, 422 and 429 — because the API returns
 * patient contact details and each read has to be traceable to a key.
 */
class AuthenticateApiKey
{
    public const ATTRIBUTE = 'api_key';

    /** Set by controllers: how many records a response carried, for the access log. */
    public const ROWS_ATTRIBUTE = 'api_rows';

    private const STARTED_ATTRIBUTE = 'api_started_at';

    /** last_used_at is only rewritten when older than this, so reads do not each cost a write. */
    private const LAST_USED_RESOLUTION_SECONDS = 60;

    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        $request->attributes->set(self::STARTED_ATTRIBUTE, microtime(true));

        $key = ApiKey::findActive((string) $request->bearerToken());

        if ($key === null) {
            return response()->json(['message' => 'Missing, invalid, expired or revoked API key.'], 401);
        }

        $request->attributes->set(self::ATTRIBUTE, $key);

        foreach ($abilities as $ability) {
            if (! $key->allows($ability)) {
                return response()->json(['message' => "This API key does not have the {$ability} permission."], 403);
            }
        }

        if ($key->last_used_at === null || $key->last_used_at->diffInSeconds(now()) >= self::LAST_USED_RESOLUTION_SECONDS) {
            $key->forceFill(['last_used_at' => now(), 'last_used_ip' => $request->ip()])->saveQuietly();
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $key = $request->attributes->get(self::ATTRIBUTE);

        if (! $key instanceof ApiKey) {
            return;
        }

        $started = (float) $request->attributes->get(self::STARTED_ATTRIBUTE, microtime(true));
        $query = $request->getQueryString();

        try {
            ApiRequestLog::create([
                'api_key_id' => $key->id,
                'method' => $request->method(),
                'path' => mb_substr('/'.ltrim($request->path(), '/'), 0, 255),
                'query' => $query !== null ? mb_substr(urldecode($query), 0, 2000) : null,
                'status' => $response->getStatusCode(),
                'rows' => $request->attributes->get(self::ROWS_ATTRIBUTE),
                'ip' => $request->ip(),
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
            ]);
        } catch (Throwable $e) {
            // The response is already sent; a failed log line must not surface as an error.
            report($e);
        }
    }
}
