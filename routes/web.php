<?php

use App\Http\Controllers\LicenseController;
use Illuminate\Support\Facades\Route;

// Auth-protected admin routes (use Laravel's built-in auth or basic auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/', fn() => redirect()->route('licenses.index'));

    Route::get('/licenses',                   [LicenseController::class, 'index'])->name('licenses.index');
    Route::get('/licenses/create',            [LicenseController::class, 'create'])->name('licenses.create');
    Route::post('/licenses',                  [LicenseController::class, 'store'])->name('licenses.store');
    Route::get('/licenses/{license}',         [LicenseController::class, 'show'])->name('licenses.show');
    Route::get('/licenses/{license}/edit',    [LicenseController::class, 'edit'])->name('licenses.edit');
    Route::put('/licenses/{license}',         [LicenseController::class, 'update'])->name('licenses.update');

    Route::post('/licenses/{license}/token',   [LicenseController::class, 'generateToken'])->name('licenses.token');
    Route::post('/licenses/{license}/renew',   [LicenseController::class, 'renew'])->name('licenses.renew');
    Route::post('/licenses/{license}/revoke',  [LicenseController::class, 'revoke'])->name('licenses.revoke');
    Route::post('/licenses/{license}/suspend', [LicenseController::class, 'suspend'])->name('licenses.suspend');
    Route::post('/licenses/{license}/activate',[LicenseController::class, 'activate'])->name('licenses.activate');
});
