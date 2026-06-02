<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ed25519 Private Key (BASE64 encoded)
    | Generate: php artisan license:keygen
    | NEVER share this key. NEVER commit to git. Store in .env only.
    |--------------------------------------------------------------------------
    */
    'private_key' => env('LICENSE_PRIVATE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Ed25519 Public Key (BASE64 encoded)
    | This is safe to distribute to clients via LICENSE_PUBLIC_KEY in their .env
    |--------------------------------------------------------------------------
    */
    'public_key' => env('LICENSE_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Fingerprint Secret (64 hex chars)
    | Used to generate the 'fpr' claim inside tokens.
    | Clients receive a DERIVED verification key, not this secret.
    | Generate: bin2hex(random_bytes(32))
    |--------------------------------------------------------------------------
    */
    'fingerprint_secret' => env('LICENSE_FINGERPRINT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Support Emails shown on expired license page (on client apps)
    | Clients configure this in their own .env
    |--------------------------------------------------------------------------
    */
    'support_emails' => [
        env('LICENSE_SUPPORT_EMAIL_1', 'support@yourcompany.com'),
        env('LICENSE_SUPPORT_EMAIL_2', 'billing@yourcompany.com'),
    ],
];
