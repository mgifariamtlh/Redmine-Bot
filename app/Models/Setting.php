<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    /** Key berisi kredensial; disimpan terenkripsi di database. */
    public const SECRET_KEYS = ['redmine_api_key', 'telegram_bot_token'];

    /** Nilai awal tiap key (dipakai seeder dan sebagai fallback). */
    public const DEFAULTS = [
        'redmine_base_url'   => 'https://redmine.recis.co.id',
        'redmine_api_key'    => '',
        'telegram_bot_token' => '',
        'cron_schedule_time' => '16:00',
    ];

    protected $fillable = ['key', 'value'];

    /**
     * Ambil nilai setting, mis. Setting::get('redmine_api_key').
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::where('key', $key)->value('value');

        if ($value === null || $value === '') {
            return $default ?? static::DEFAULTS[$key] ?? null;
        }

        if (in_array($key, static::SECRET_KEYS, true)) {
            try {
                return Crypt::decryptString($value);
            } catch (DecryptException) {
                return $default; // APP_KEY berubah / data rusak
            }
        }

        return $value;
    }

    /**
     * Simpan nilai setting (membuat baris baru bila belum ada).
     */
    public static function set(string $key, ?string $value): void
    {
        if ($value !== null && $value !== '' && in_array($key, static::SECRET_KEYS, true)) {
            $value = Crypt::encryptString($value);
        }

        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Apakah key ini sudah berisi nilai.
     */
    public static function has(string $key): bool
    {
        return filled(static::where('key', $key)->value('value'));
    }
}
