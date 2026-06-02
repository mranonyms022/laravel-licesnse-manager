<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Credentials
    | Set these in .env — no users table needed
    |--------------------------------------------------------------------------
    | ADMIN_EMAIL=admin@yourcompany.com
    | ADMIN_PASSWORD=your_hashed_password
    |
    | Generate hashed password:
    | php artisan tinker → bcrypt('yourpassword')
    |--------------------------------------------------------------------------
    */
    'email'    => env('ADMIN_EMAIL', 'admin@example.com'),
    'password' => env('ADMIN_PASSWORD'), // bcrypt hashed
];
