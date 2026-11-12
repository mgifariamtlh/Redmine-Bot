<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramService
{
    /**
     * Kirim pesan personal (Direct Message) ke satu chat ID.
     */
    public function sendMessage(string $chatId, string $text): void
    {
        $token = Setting::get('telegram_bot_token');

        if (blank($token)) {
            throw new RuntimeException('Telegram Bot Token belum diatur di menu Settings.');
        }

        $response = Http::timeout(15)->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ]);

        if ($response->failed()) {
            $reason = $response->json('description') ?? "HTTP {$response->status()}";
            throw new RuntimeException("Telegram gagal mengirim pesan: {$reason}");
        }
    }
}
