<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LicenseController;
use Illuminate\Support\Facades\Route;

// ── Auth Routes ───────────────────────────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Admin Routes (protected) ──────────────────────────────────────────────────
Route::middleware(['admin.auth'])->group(function () {

    Route::get('/', fn() => redirect()->route('licenses.index'));

    // Licenses CRUD
    Route::get('/licenses',                    [LicenseController::class, 'index'])->name('licenses.index');
    Route::get('/licenses/create',             [LicenseController::class, 'create'])->name('licenses.create');
    Route::post('/licenses',                   [LicenseController::class, 'store'])->name('licenses.store');
    Route::get('/licenses/{license}',          [LicenseController::class, 'show'])->name('licenses.show');
    Route::get('/licenses/{license}/edit',     [LicenseController::class, 'edit'])->name('licenses.edit');
    Route::put('/licenses/{license}',          [LicenseController::class, 'update'])->name('licenses.update');

    // License Actions
    Route::post('/licenses/{license}/token',   [LicenseController::class, 'generateToken'])->name('licenses.token');
    Route::post('/licenses/{license}/renew',   [LicenseController::class, 'renew'])->name('licenses.renew');
    Route::post('/licenses/{license}/revoke',  [LicenseController::class, 'revoke'])->name('licenses.revoke');
    Route::post('/licenses/{license}/suspend', [LicenseController::class, 'suspend'])->name('licenses.suspend');
    Route::post('/licenses/{license}/activate', [LicenseController::class, 'activate'])->name('licenses.activate');
    Route::delete('/licenses/{license}',       [LicenseController::class, 'destroy'])->name('licenses.destroy');
});
