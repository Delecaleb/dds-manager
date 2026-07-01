<?php

use App\Http\Controllers\AgingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FinancialController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('patients/data', [PatientController::class, 'data'])->name('patients.data');
    Route::get('patients/{id}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('patients/{id}/treatment-plans', [PatientController::class, 'showTreatment']);
    Route::get('patients/{id}/ar', [PatientController::class, 'showArLive']);
    Route::get('patients/{id}/family', [PatientController::class, 'showFamily']);
    Route::get('patients/{id}/employer', [PatientController::class, 'showEmployer']);
    Route::get('aging', [AgingController::class, 'index'])->name('aging.index');
    Route::get('aging/data', [AgingController::class, 'data'])->name('aging.data');
    
    Route::get('financials', [FinancialController::class, 'index'])->name('financials.index');
    Route::get('financials/data', [FinancialController::class, 'data'])->name('financials.data');
    
    Route::get('eod', function () {
        return view('eod.index');
    })->name('eod.index');
    Route::get('huddle', function () {
        return view('huddle.index');
    })->name('huddle.index');
    Route::get('operations', function () {
        return view('operations.index');
    })->name('operations.index');
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/data', [CalendarController::class, 'getData'])->name('calendar.data');
    Route::get('calendar/resources', [CalendarController::class, 'getResources'])->name('calendar.resources');
    Route::get('snapshot', function () {
        return view('snapshot.index');
    })->name('snapshot.index');
    Route::get('front-office', function () {
        return view('front-office.index');
    })->name('front-office.index');
});

require __DIR__ . '/auth.php';
