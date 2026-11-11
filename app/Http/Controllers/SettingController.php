<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        // Kredensial tidak pernah dikirim ke view; hanya status terisi atau belum.
        $settings = [
            'redmine_base_url'   => Setting::get('redmine_base_url'),
            'cron_schedule_time' => Setting::get('cron_schedule_time'),
            'has_api_key'        => Setting::has('redmine_api_key'),
            'has_bot_token'      => Setting::has('telegram_bot_token'),
        ];

        // Data dummy (belum ada tabel log).
        $logs = [
            ['level' => 'success', 'source' => 'Redmine',  'message' => 'Sinkronisasi issue selesai (48 issue)',       'time' => '2026-10-30 15:58'],
            ['level' => 'info',    'source' => 'Telegram', 'message' => 'Reminder deploy #1063 terkirim ke @rudi_h',   'time' => '2026-10-30 15:45'],
            ['level' => 'error',   'source' => 'Telegram', 'message' => 'Gagal kirim pesan ke Chat ID 58812345 (403)', 'time' => '2026-10-30 14:10'],
            ['level' => 'warning', 'source' => 'Redmine',  'message' => 'Custom field "Jam Deploy" kosong pada #1071', 'time' => '2026-10-30 13:02'],
            ['level' => 'error',   'source' => 'Redmine',  'message' => 'Timeout saat memanggil /issues.json',         'time' => '2026-10-30 09:30'],
        ];

        return view('settings.index', compact('settings', 'logs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'redmine_base_url'   => ['required', 'url', 'max:255'],
            'redmine_api_key'    => ['nullable', 'string', 'max:255'],
            'telegram_bot_token' => ['nullable', 'string', 'max:255'],
            'cron_schedule_time' => ['required', 'date_format:H:i'],
        ]);

        Setting::set('redmine_base_url', rtrim($data['redmine_base_url'], '/'));
        Setting::set('cron_schedule_time', $data['cron_schedule_time']);

        // Kolom kredensial yang dikosongkan berarti "jangan diubah".
        foreach (Setting::SECRET_KEYS as $key) {
            if (filled($data[$key] ?? null)) {
                Setting::set($key, $data[$key]);
            }
        }

        return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
