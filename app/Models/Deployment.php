<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deployment extends Model
{
    /** Status yang ditarik dari Redmine (status Closed sengaja tidak disinkronkan). */
    public const STATUSES = ['test-passed', 'deployed'];

    protected $fillable = [
        'issue_id',
        'subject',
        'redmine_user_id',
        'deploy_date',
        'pic_name',
        'deploy_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'deploy_date' => 'date',
        ];
    }

    /** Link ke task di Redmine, berdasarkan Redmine Base URL di Settings. */
    protected function url(): Attribute
    {
        return Attribute::get(function () {
            // Dibaca sekali per request agar tabel tidak memicu query per baris.
            static $baseUrl;
            $baseUrl ??= rtrim((string) Setting::get('redmine_base_url'), '/');

            return "{$baseUrl}/issues/{$this->issue_id}";
        });
    }

    /** Mapping Telegram milik PIC (dicocokkan lewat Redmine user ID). */
    public function mapping(): BelongsTo
    {
        return $this->belongsTo(TelegramMapping::class, 'redmine_user_id', 'redmine_user_id');
    }
}
