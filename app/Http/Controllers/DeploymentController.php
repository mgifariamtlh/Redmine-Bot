<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeploymentController extends Controller
{
    private const STATUSES = ['test-passed', 'deployed', 'closed'];

    public function index(Request $request): View
    {
        $status = $request->query('status', 'test-passed');
        if (! in_array($status, self::STATUSES, true)) {
            $status = 'test-passed';
        }

        // Data dummy (tahap desain UI). Nantinya diambil dari Redmine API / database.
        $deployments = collect([
            ['issue_id' => 1042, 'title' => 'Perbaikan modul login SSO',          'pic' => 'Budi Santoso', 'deploy_time' => '09:00', 'status' => 'test-passed'],
            ['issue_id' => 1057, 'title' => 'Update API laporan keuangan',        'pic' => 'Siti Aminah',  'deploy_time' => '11:30', 'status' => 'test-passed'],
            ['issue_id' => 1063, 'title' => 'Migrasi database server staging',    'pic' => 'Rudi Hartono', 'deploy_time' => '13:00', 'status' => 'test-passed'],
            ['issue_id' => 1030, 'title' => 'Hotfix notifikasi email pengguna',   'pic' => 'Dewi Lestari', 'deploy_time' => '15:00', 'status' => 'deployed'],
            ['issue_id' => 1025, 'title' => 'Deploy fitur export PDF invoice',    'pic' => 'Andi Wijaya',  'deploy_time' => '16:30', 'status' => 'deployed'],
            ['issue_id' => 1011, 'title' => 'Optimasi query dashboard',           'pic' => 'Budi Santoso', 'deploy_time' => '10:00', 'status' => 'closed'],
            ['issue_id' => 1008, 'title' => 'Perbaikan validasi form registrasi', 'pic' => 'Siti Aminah',  'deploy_time' => '14:00', 'status' => 'closed'],
        ]);

        return view('deployments.index', [
            'deployments' => $deployments->where('status', $status)->values(),
            'status'      => $status,
            'counts'      => $deployments->countBy('status'),
        ]);
    }

    public function sync(): RedirectResponse
    {
        // TODO: panggil service penarikan data Redmine API.
        return back()->with('success', 'Sinkronisasi data Redmine selesai.');
    }

    public function notify(int $issueId): RedirectResponse
    {
        // TODO: kirim pesan Telegram personal ke PIC terkait.
        return back()->with('success', "Reminder deploy #{$issueId} dikirim ke PIC.");
    }
}
