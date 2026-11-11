<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramMapping extends Model
{
    protected $fillable = [
        'name',
        'redmine_user_id',
        'telegram_username',
        'chat_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
