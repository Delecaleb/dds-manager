<?php

use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\MeController;
use App\Models\ApiKey;
use Illuminate\Support\Facades\Route;

/*
| DEVELOPER API (read-only), served under /api. Keys are issued per location from
| Administration → Developer API; the key decides the location, never the URL.
| Each route authenticates once (`api.key:<permission>`), then applies the key's rate limit.
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('me', MeController::class)->middleware(['api.key', 'throttle:developer-api'])->name('me');

    Route::middleware(['api.key:'.ApiKey::ABILITY_BOOKINGS, 'throttle:developer-api'])->group(function () {
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    });
});
