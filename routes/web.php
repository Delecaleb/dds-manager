<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('patients', function () {
    return view('patients.index');
})->name('patients.index');
Route::get('eod', function () {
    return view('eod.index');
})->name('eod.index');
Route::get('huddle', function () {
    return view('huddle.index');
})->name('huddle.index');
Route::get('operations', function () {
    return view('operations.index');
})->name('operations.index');
Route::get('calendar', function () {
    return view('calendar.index');
})->name('calendar.index');
Route::get('snapshot', function () {
    return view('snapshot.index');
})->name('snapshot.index');
Route::get('front-office', function () {
    return view('front-office.index');
})->name('front-office.index');

require __DIR__.'/auth.php';
