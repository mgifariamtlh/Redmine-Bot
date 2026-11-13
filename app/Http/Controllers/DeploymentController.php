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

        Setting::set('last_sync_at', now()->toDateTimeString());

        $unmapped = collect($issues)->pluck('redmine_user_id')->filter()->unique()
            ->reject(fn ($id) => isset($names[(string) $id]))->count();

        $message = 'Sinkronisasi selesai: '.count($issues).' task diperbarui.';
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
