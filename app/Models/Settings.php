<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    public $timestamps   = false;
    protected $primaryKey = 'key';
    public $incrementing  = false;
    protected $keyType    = 'string';

    // ─── Static helpers ───────────────────────────────────────────────

    public static function set(string $key, string $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value'      => Crypt::encryptString($value),
                'updated_at' => now(),
            ]
        );
    }

    public static function get(string $key): ?string
    {
        $row = static::find($key);
        if (! $row) return null;

        try {
            return Crypt::decryptString($row->value);
        } catch (\Throwable) {
            return null; // Tampered or wrong APP_KEY
        }
    }
}
