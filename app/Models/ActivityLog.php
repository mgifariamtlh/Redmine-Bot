<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['level', 'source', 'message', 'user_id', 'context'];

    protected function casts(): array
    {
        return [
            'context'    => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Catat satu aktivitas. Pengguna diambil otomatis dari sesi login;
     * kosong bila dijalankan scheduler / bot Telegram. Tidak pernah melempar error
     * agar kegagalan pencatatan tidak menggagalkan aksi utama.
     */
    public static function record(string $level, string $source, string $message, array $context = []): void
    {
        try {
            static::create([
                'level'   => $level,
                'source'  => $source,
                'message' => $message,
                'user_id' => Auth::id(),
                'context' => $context ?: null,
            ]);
        } catch (Throwable $e) {
            Log::warning('Gagal mencatat activity log', ['error' => $e->getMessage(), 'message' => $message]);
        }
    }

    public static function success(string $source, string $message, array $context = []): void
    {
        static::record('success', $source, $message, $context);
    }

    public static function info(string $source, string $message, array $context = []): void
    {
        static::record('info', $source, $message, $context);
    }

    public static function warning(string $source, string $message, array $context = []): void
    {
        static::record('warning', $source, $message, $context);
    }

    public static function error(string $source, string $message, array $context = []): void
    {
        static::record('error', $source, $message, $context);
    }
}
