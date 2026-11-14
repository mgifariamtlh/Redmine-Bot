<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Setting;
use App\Models\TelegramMapping;
use App\Services\RedmineService;
use App\Services\TelegramService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->paginate(20)
            ->withQueryString();

        return view('deployments.index', [
            'deployments' => $deployments,
            'status'      => $status,
            'counts'      => Deployment::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'lastSync'    => Setting::get('last_sync_at'),
        ]);
    }

    public function sync(RedmineService $redmine): RedirectResponse
    {
        // Beberapa request x timeout HTTP bisa melewati batas PHP (60 dtk di XAMPP); beri ruang agar
        // yang tertangkap adalah timeout HTTP (pesan rapi), bukan fatal error PHP.
        set_time_limit(300);

        try {
            $issues = $redmine->fetchDeployments();
        } catch (RuntimeException|ConnectionException $e) {
            return back()->with('error', 'Sinkronisasi gagal: '.$e->getMessage());
        }

        // Nama PIC diprioritaskan dari telegram_mappings (cocok lewat redmine_user_id).
        $names = TelegramMapping::pluck('name', 'redmine_user_id');

        DB::transaction(function () use ($issues, $names) {
            foreach ($issues as $row) {
                $row['pic_name'] = $names[(string) $row['redmine_user_id']] ?? $row['pic_name'];

                Deployment::updateOrCreate(['issue_id' => $row['issue_id']], $row);
            }
        });

        // Hapus data lokal yang di Redmine sudah Closed. Hanya record lokal yang TIDAK ikut
        // ditarik di atas yang dicek (yang ditarik pasti Test Passed / Deployed), sehingga
        // data di luar rentang tanggal / batas limit tidak terhapus hanya karena tak terambil.
        $fetchedIds = collect($issues)->pluck('issue_id');
        $localIds = Deployment::whereNotIn('issue_id', $fetchedIds)->pluck('issue_id')->all();

        try {
            $closedIds = $redmine->fetchClosedIssueIds($localIds);
        } catch (RuntimeException|ConnectionException $e) {
            // Data sudah tersimpan; hanya pembersihan yang gagal.
            Setting::set('last_sync_at', now()->toDateTimeString());

            return back()->with('error', 'Data diperbarui ('.count($issues).' task), tetapi pengecekan status Closed gagal: '.$e->getMessage());
        }

        $deleted = $closedIds ? Deployment::whereIn('issue_id', $closedIds)->delete() : 0;

        Setting::set('last_sync_at', now()->toDateTimeString());

        $unmapped = collect($issues)->pluck('redmine_user_id')->filter()->unique()
            ->reject(fn ($id) => isset($names[(string) $id]))->count();

        $message = 'Sync selesai: '.count($issues)." data diperbarui, {$deleted} data closed dihapus.";
        if ($unmapped > 0) {
            $message .= " {$unmapped} PIC belum punya User Mapping Telegram.";
        }

        return back()->with('success', $message);
    }

    public function sendReminder(Deployment $deployment, TelegramService $telegram): RedirectResponse
    {
        $mapping = $deployment->mapping;

        if (! $mapping || ! $mapping->is_active) {
            return back()->with('error', 'PIC belum punya User Mapping Telegram yang aktif.');
        }

        $time = trim($deployment->deploy_date?->translatedFormat('d M Y').' '.$deployment->deploy_time) ?: 'belum ditentukan';
        $text = "🔔 <b>Reminder Deploy</b>\n"
            ."Halo ".e($mapping->name).", task <a href=\"".e($deployment->url)."\">#{$deployment->issue_id}</a> "
            .e($deployment->subject)." dijadwalkan deploy pada <b>{$time}</b>.";

        try {
            $telegram->sendMessage($mapping->chat_id, $text);
        } catch (RuntimeException|ConnectionException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Reminder #{$deployment->issue_id} dikirim ke {$mapping->name}.");
    }
}
