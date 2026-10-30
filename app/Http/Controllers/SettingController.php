<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        // Data dummy (tahap desain UI). Nantinya diambil dari tabel settings & logs.
        $settings = [
            'redmine_url'      => 'https://redmine.example.com',
            'telegram_token'   => '',
            'scheduler_time'   => '16:00',
            'scheduler_active' => true,
        ];

        $logs = [
            ['level' => 'success', 'source' => 'Redmine',  'message' => 'Sinkronisasi issue selesai (48 issue)',       'time' => '2026-10-30 15:58'],
            ['level' => 'info',    'source' => 'Telegram', 'message' => 'Reminder deploy #1063 terkirim ke @rudi_h',   'time' => '2026-10-30 15:45'],
            ['level' => 'error',   'source' => 'Telegram', 'message' => 'Gagal kirim pesan ke Chat ID 58812345 (403)', 'time' => '2026-10-30 14:10'],
            ['level' => 'warning', 'source' => 'Redmine',  'message' => 'Custom field "Jam Deploy" kosong pada #1071', 'time' => '2026-10-30 13:02'],
            ['level' => 'error',   'source' => 'Redmine',  'message' => 'Timeout saat memanggil /issues.json',         'time' => '2026-10-30 09:30'],
        ];

        return view('settings.index', compact('settings', 'logs'));
    }

    public function updateIntegration(Request $request): RedirectResponse
    {
        $request->validate([
            'redmine_url'    => ['required', 'url'],
            'telegram_token' => ['nullable', 'string'],
        ]);

        // TODO: simpan ke database (token dienkripsi).
        return back()->with('success', 'Konfigurasi API & integrasi disimpan.');
    }

    public function updateScheduler(Request $request): RedirectResponse
    {
        $request->validate([
            'scheduler_time' => ['required', 'date_format:H:i'],
        ]);

        // TODO: simpan ke database. Gunakan $request->boolean('scheduler_active')
        // karena checkbox tidak terkirim saat nonaktif.
        return back()->with('success', 'Pengaturan scheduler disimpan.');
    }
}
