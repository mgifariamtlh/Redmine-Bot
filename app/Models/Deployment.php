<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deployment extends Model
{
    public const STATUSES = ['test-passed', 'deployed', 'closed'];

    protected $fillable = [
        'issue_id',
        'subject',
        'redmine_user_id',
        'pic_name',
        'deploy_time',
        'status',
    ];

    /** Mapping Telegram milik PIC (dicocokkan lewat Redmine user ID). */
    public function mapping(): BelongsTo
    {
        return $this->belongsTo(TelegramMapping::class, 'redmine_user_id', 'redmine_user_id');
    }
}
