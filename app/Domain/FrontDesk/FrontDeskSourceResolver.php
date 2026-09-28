<?php

namespace App\Domain\FrontDesk;

use App\Domain\Support\ClinicRegistry;
use Illuminate\Http\Request;

/**
 * Picks the AI Front Desk data source for a request.
 *
 * Until a call platform is connected, that is the empty source. `?preview=1` switches the
 * session to the sample source for UI review, `?preview=0` switches it back. When the
 * platform source exists, it replaces the empty source here and nowhere else.
 */
final class FrontDeskSourceResolver
{
    private const SESSION_KEY = 'front_desk.preview';

    public function __construct(private readonly ClinicRegistry $clinics) {}

    public function forRequest(Request $request): FrontDeskSource
    {
        if ($request->has('preview') && $request->hasSession()) {
            $request->session()->put(self::SESSION_KEY, $request->boolean('preview'));
        }

        $preview = $request->hasSession() && $request->session()->get(self::SESSION_KEY, false);

        return $preview
            ? new SampleFrontDeskSource($this->clinics)
            : new EmptyFrontDeskSource($this->clinics);
    }
}
