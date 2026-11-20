<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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

        // 500 aktivitas terakhir; search, sort, dan pagination ditangani DataTables.
        $logs = ActivityLog::with('user:id,name')->latest('id')->limit(500)->get();

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

        $baseUrl = rtrim($data['redmine_base_url'], '/');
        $changes = [];

        if ($baseUrl !== Setting::get('redmine_base_url')) {
            $changes[] = 'Redmine Base URL';
        }
        if ($data['cron_schedule_time'] !== Setting::get('cron_schedule_time')) {
            $changes[] = "Jam Scheduler ({$data['cron_schedule_time']})";
        }

        Setting::set('redmine_base_url', $baseUrl);
        Setting::set('cron_schedule_time', $data['cron_schedule_time']);

        // Kolom kredensial yang dikosongkan berarti "jangan diubah".
        $secretLabels = ['redmine_api_key' => 'Redmine API Key', 'telegram_bot_token' => 'Telegram Bot Token'];
        foreach (Setting::SECRET_KEYS as $key) {
            if (filled($data[$key] ?? null)) {
                Setting::set($key, $data[$key]);
                $changes[] = $secretLabels[$key] ?? $key; // nilai kredensial tidak pernah dicatat
            }
        }

        ActivityLog::info('Settings', $changes
            ? 'Pengaturan disimpan. Diubah: '.implode(', ', $changes).'.'
            : 'Pengaturan disimpan (tidak ada perubahan).');

        return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
