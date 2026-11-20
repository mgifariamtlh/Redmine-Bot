<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Deployment;
use App\Models\Setting;
use App\Services\DeploymentSyncService;
use App\Services\TelegramService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class DeploymentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'test-passed');
        if (! in_array($status, Deployment::STATUSES, true)) {
            $status = 'test-passed';
        }

        $deployments = Deployment::with('mapping')
            ->where('status', $status)
            ->orderByDesc('deploy_date')
            ->orderBy('deploy_time')
            ->orderByDesc('issue_id')
            ->get();

        return view('deployments.index', [
            'deployments' => $deployments,
            'status'      => $status,
            'counts'      => Deployment::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'lastSync'    => Setting::get('last_sync_at'),
        ]);
    }

    public function sync(DeploymentSyncService $syncService): RedirectResponse
    {
        // Beberapa request x timeout HTTP bisa melewati batas PHP (60 dtk di XAMPP); beri ruang agar
        // yang tertangkap adalah timeout HTTP (pesan rapi), bukan fatal error PHP.
        set_time_limit(300);

        try {
            $result = $syncService->sync();
        } catch (RuntimeException|ConnectionException $e) {
            return back()->with('error', 'Sinkronisasi gagal: '.$e->getMessage());
        }

        if ($result['close_check_error'] !== null) {
            return back()->with('error', 'Data diperbarui ('.$result['fetched'].' task), tetapi pengecekan status Closed gagal: '.$result['close_check_error']);
        }

        $message = 'Sync selesai: '.$result['fetched']." data diperbarui, {$result['deleted']} data closed dihapus.";
        if ($result['unmapped'] > 0) {
            $message .= " {$result['unmapped']} PIC belum punya User Mapping Telegram.";
        }

        return back()->with('success', $message);
    }

    public function sendReminder(Deployment $deployment, TelegramService $telegram): RedirectResponse
    {
        $mapping = $deployment->mapping;

        if (! $mapping || ! $mapping->is_active) {
            ActivityLog::warning('Jadwal Deploy', "Reminder #{$deployment->issue_id} dilewati: PIC belum punya mapping Telegram aktif.");

            return back()->with('error', 'PIC belum punya User Mapping Telegram yang aktif.');
        }

        $time = trim($deployment->deploy_date?->translatedFormat('d M Y').' '.$deployment->deploy_time) ?: 'belum ditentukan';
        $text = "🔔 <b>Reminder Deploy</b>\n"
            ."Halo ".e($mapping->name).", task <a href=\"".e($deployment->url)."\">#{$deployment->issue_id}</a> "
            .e($deployment->subject)." dijadwalkan deploy pada <b>{$time}</b>.";

        try {
            $telegram->sendMessage($mapping->chat_id, $text);
        } catch (RuntimeException|ConnectionException $e) {
            ActivityLog::error('Telegram', "Gagal kirim reminder #{$deployment->issue_id} ke {$mapping->name}: {$e->getMessage()}");

            return back()->with('error', $e->getMessage());
        }

        ActivityLog::success('Telegram', "Reminder deploy #{$deployment->issue_id} dikirim manual ke {$mapping->name}.");

        return back()->with('success', "Reminder #{$deployment->issue_id} dikirim ke {$mapping->name}.");
    }
}
