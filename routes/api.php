<?php

use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\ClinicController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\PatientController;
use App\Http\Controllers\Api\V1\PatientProcedureController;
use App\Http\Controllers\Api\V1\ProcedureCodeController;
use App\Models\ApiKey;
use Illuminate\Support\Facades\Route;

/*
| DEVELOPER API (read-only), served under /api. Keys are issued per location from
| Administration → Developer API; the key decides the location, never the URL.
| Each route authenticates once (`api.key:<permission>`), then applies the key's rate limit.
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('me', MeController::class)->middleware(['api.key', 'throttle:developer-api'])->name('me');
    Route::get('clinics', [ClinicController::class, 'index'])->middleware(['api.key', 'throttle:developer-api'])->name('clinics.index');

    Route::middleware(['api.key:'.ApiKey::ABILITY_BOOKINGS, 'throttle:developer-api'])->group(function () {
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    });

    // POST only so that names, birth dates and patient lists stay out of URLs and logs; they read, never write.
    Route::middleware(['api.key:'.ApiKey::ABILITY_PATIENT_MATCH, 'throttle:developer-api'])->group(function () {
        Route::post('patients/match', [PatientController::class, 'match'])->name('patients.match');
        Route::get('patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
    });

    Route::middleware(['api.key:'.ApiKey::ABILITY_PROCEDURES, 'throttle:developer-api'])->group(function () {
        Route::post('patients/procedures', PatientProcedureController::class)->name('patients.procedures');
        Route::get('procedure-codes', [ProcedureCodeController::class, 'index'])->name('procedure-codes.index');
    });
});
