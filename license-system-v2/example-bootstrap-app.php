<?php

/**
 * ════════════════════════════════════════════════════════
 *  CLIENT PROJECT: bootstrap/app.php
 *
 *  Ye file client ke Laravel project ki bootstrap/app.php hai.
 *  Yahan dikhaya gaya hai ki exactly kahan kya add karna hai.
 * ════════════════════════════════════════════════════════
 */

// ╔══════════════════════════════════════════════════════╗
// ║  STEP 1: LICENSE GUARD — BILKUL PEHLI LINE           ║
// ║  Ye Laravel ke boot se BHI PEHLE chalti hai          ║
// ║  Package delete ho ya composer.json edit ho —        ║
// ║  ye tab bhi kaam karti hai.                          ║
// ╚══════════════════════════════════════════════════════╝
require_once __DIR__ . '/license-guard.php';
// ─────────────────────────────────────────────────────────


use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ╔══════════════════════════════════════════════════════╗
        // ║  STEP 2: LICENSE MIDDLEWARE — WEB GROUP MEIN         ║
        // ║  Ye package wala middleware hai — double protection  ║
        // ║  Agar package available hai to ye bhi check karega   ║
        // ╚══════════════════════════════════════════════════════╝
        $middleware->prependToGroup('web', \YourVendor\LaravelLicense\Http\Middleware\LicenseCheck::class);
        // ─────────────────────────────────────────────────────────

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();


/*
 * ════════════════════════════════════════════════════════════════
 *  DUAL PROTECTION EXPLAINED:
 *
 *  require_once 'license-guard.php'
 *      → PHP level, runs before ANYTHING
 *      → No dependency on Composer/Laravel/package
 *      → Reads .env manually, verifies Ed25519 signature
 *      → If guard file itself is deleted → require_once fails → App crashes
 *        (developer ko pata chal jaata hai)
 *
 *  LicenseCheck middleware (package wala)
 *      → Laravel level, runs for every web request
 *      → Uses package's TokenVerifier (full-featured)
 *      → Better error messages, admin panel, grace period logging
 *      → If package is removed → middleware class not found → App crashes
 *        (developer ko pata chal jaata hai)
 *
 *  DONO milke ensure karte hain:
 *  - Package delete → crash (visible)
 *  - Guard file delete → crash (visible)
 *  - Middleware hatao → guard file still runs
 *  - Guard file hatao → middleware still runs
 *  - Dono hatao → composer autoload fails at bootstrap
 * ════════════════════════════════════════════════════════════════
 */
