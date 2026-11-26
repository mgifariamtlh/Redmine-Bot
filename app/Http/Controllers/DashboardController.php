<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Deployment;
use App\Models\Setting;
use App\Models\TelegramMapping;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = today();

        $deploysToday = Deployment::with('mapping:id,redmine_user_id,name')
            ->whereDate('deploy_date', $today)
            ->orderBy('deploy_time')
            ->orderBy('issue_id')
            ->get();

        $stats = [
            'total_users'   => TelegramMapping::count(),
            'active_users'  => TelegramMapping::where('is_active', true)->count(),
            'new_users'     => TelegramMapping::where('created_at', '>=', $today->copy()->startOfWeek())->count(),
            'tasks_today'   => $deploysToday->count(),
            'tasks_done'    => $deploysToday->where('status', 'deployed')->count(),
            'pending_total' => Deployment::where('status', 'test-passed')->count(),
            // Hanya status konfigurasi; kesehatan koneksi sebenarnya dicatat di Log Aktivitas.
            'redmine'       => Setting::has('redmine_api_key'),
            'telegram'      => Setting::has('telegram_bot_token'),
            'last_sync'     => Setting::get('last_sync_at'),
        ];

        // Jadwal deploy per hari pada minggu berjalan (Senin-Minggu).
        $weekStart = $today->copy()->startOfWeek();
        $perDay = Deployment::whereBetween('deploy_date', [$weekStart, $weekStart->copy()->endOfWeek()])
            ->get(['deploy_date', 'status'])
            ->groupBy(fn ($d) => $d->deploy_date->toDateString());

        $week = collect(range(0, 6))->map(function ($i) use ($weekStart, $perDay) {
            $date = $weekStart->copy()->addDays($i);
            $rows = $perDay->get($date->toDateString(), collect());

            return [
                'label'    => $date->translatedFormat('D'),
                'total'    => $rows->count(),
                'deployed' => $rows->where('status', 'deployed')->count(),
                'today'    => $date->isToday(),
            ];
        });

        $logs = ActivityLog::latest('id')->limit(6)->get();

        return view('dashboard', compact('stats', 'deploysToday', 'week', 'logs'));
    }
}
