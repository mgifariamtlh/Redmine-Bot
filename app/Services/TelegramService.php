<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramService
{
    /**
     * Kirim pesan personal (Direct Message) ke satu chat ID.
     *
     * @param  string  $parseMode  HTML atau Markdown
     */
    public function sendMessage(string $chatId, string $text, string $parseMode = 'HTML'): void
    {
        $response = $this->call('sendMessage', [
            'chat_id'                  => $chatId,
            'text'                     => $text,
            'parse_mode'               => $parseMode,
            'disable_web_page_preview' => true,
        ]);

        if ($response->failed()) {
            $reason = $response->json('description') ?? "HTTP {$response->status()}";
            throw new RuntimeException("Telegram gagal mengirim pesan: {$reason}");
        }
    }

    /**
     * Daftarkan URL webhook; Telegram akan menyertakan $secret di header
     * X-Telegram-Bot-Api-Secret-Token pada setiap update.
     */
    public function setWebhook(string $url, string $secret): void
    {
        $response = $this->call('setWebhook', [
            'url'             => $url,
            'secret_token'    => $secret,
            'allowed_updates' => ['message'],
        ]);

        if ($response->failed() || ! $response->json('ok')) {
            $reason = $response->json('description') ?? "HTTP {$response->status()}";
            throw new RuntimeException("Telegram menolak setWebhook: {$reason}");
        }
    }

    private function call(string $method, array $payload): Response
    {
        $token = Setting::get('telegram_bot_token');

        if (blank($token)) {
            throw new RuntimeException('Telegram Bot Token belum diatur di menu Settings.');
        }

        return Http::timeout(15)->post("https://api.telegram.org/bot{$token}/{$method}", $payload);
    }
}
