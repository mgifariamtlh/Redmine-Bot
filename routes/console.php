<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengingat deploy ke PIC via Telegram. Jam kirim dibaca dari Settings
// (`cron_schedule_time`, format H:i), jadi scheduler dicek tiap menit dan hanya
// menjalankan command saat jam sekarang sama dengan jam di Settings.
// Untuk testing lokal (`php artisan schedule:work`), hapus ->when(...) agar jalan tiap menit.
Schedule::command('deploy:notify-pic')
    ->everyMinute()
    ->when(function () {
        try {
            return now()->format('H:i') === Setting::get('cron_schedule_time');
        } catch (Throwable) {
            return false; // DB belum siap; coba lagi menit berikutnya
        }
    })
    ->withoutOverlapping();
