<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {url? : URL publik HTTPS (default: APP_URL/telegram/webhook)}';

    protected $description = 'Daftarkan webhook bot Telegram beserta secret token pengaman';

    public function handle(TelegramService $telegram): int
    {
        $url = $this->argument('url') ?: rtrim(config('app.url'), '/').'/telegram/webhook';

        if (! str_starts_with($url, 'https://')) {
            $this->error("Telegram hanya menerima webhook HTTPS (diberikan: {$url}).");
            $this->line('Saat development di XAMPP, gunakan tunnel (mis. ngrok) lalu: php artisan telegram:set-webhook https://xxxx.ngrok-free.app/telegram/webhook');

            return self::FAILURE;
        }

        // Secret baru tiap pendaftaran; yang lama otomatis tidak berlaku.
        $secret = Str::random(48);

        try {
            $telegram->setWebhook($url, $secret);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        Setting::set('telegram_webhook_secret', $secret);
        $this->info("Webhook terdaftar: {$url}");

        return self::SUCCESS;
    }
}
