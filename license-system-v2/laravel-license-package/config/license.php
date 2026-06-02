<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LICENSE_TOKEN
    | The signed Ed25519 JWT token received from the license server.
    | Paste it in your .env: LICENSE_TOKEN=eyJhbGci...
    |--------------------------------------------------------------------------
    */
    'token' => env('LICENSE_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | LICENSE_PUBLIC_KEY
    | Ed25519 public key (base64) from your license server.
    | Used to verify the token signature. Safe to share.
    |--------------------------------------------------------------------------
    */
    'public_key' => env('LICENSE_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | LICENSE_FPR_KEY
    | Fingerprint verification HMAC key (base64 encoded).
    | Provided by the license server during onboarding.
    | This is NOT the private fingerprint secret — it's derived from it.
    |--------------------------------------------------------------------------
    */
    'fpr_key' => env('LICENSE_FPR_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Support Info — shown on the expired license page
    |--------------------------------------------------------------------------
    */
    'app_name'       => env('LICENSE_APP_NAME', env('APP_NAME', 'Application')),
    'support_emails' => array_filter(explode(',', env('LICENSE_SUPPORT_EMAILS', 'support@yourcompany.com'))),
    'support_phone'  => env('LICENSE_SUPPORT_PHONE', ''),

    /*
    |--------------------------------------------------------------------------
    | Verification cache TTL (seconds)
    | How long to cache a successful verification result.
    | Token is cryptographically verified, so cache is just for performance.
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => env('LICENSE_CACHE_TTL', 300), // 5 minutes
];
