<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Deployment;
use App\Models\Setting;
use App\Models\TelegramMapping;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Sinkronisasi data deployment dari Redmine ke tabel lokal.
 * Dipakai oleh tombol Sync di web dan oleh command pengingat deploy.
 */
class DeploymentSyncService
{
    public function __construct(private RedmineService $redmine) {}

    /**
     * @return array{fetched: int, deleted: int, unmapped: int, close_check_error: ?string}
     *
     * @throws RuntimeException|ConnectionException bila penarikan data dari Redmine gagal
     */
    public function sync(): array
    {
        try {
            $issues = $this->redmine->fetchDeployments();
        } catch (RuntimeException|ConnectionException $e) {
            ActivityLog::error('Redmine', 'Sinkronisasi gagal: '.$e->getMessage());

            throw $e;
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

        $deleted = 0;
        $closeCheckError = null;

        try {
            $closedIds = $this->redmine->fetchClosedIssueIds($localIds);
            $deleted = $closedIds ? Deployment::whereIn('issue_id', $closedIds)->delete() : 0;
        } catch (RuntimeException|ConnectionException $e) {
            // Data sudah tersimpan; hanya pembersihan yang gagal.
            $closeCheckError = $e->getMessage();
        }

        Setting::set('last_sync_at', now()->toDateTimeString());

        $unmapped = collect($issues)->pluck('redmine_user_id')->filter()->unique()
            ->reject(fn ($id) => isset($names[(string) $id]))->count();

        $summary = count($issues)." data diperbarui, {$deleted} data closed dihapus";
        if ($unmapped > 0) {
            $summary .= ", {$unmapped} PIC belum ter-mapping";
        }

        if ($closeCheckError !== null) {
            ActivityLog::warning('Redmine', "Sinkronisasi selesai ({$summary}), tetapi pengecekan status Closed gagal: {$closeCheckError}");
        } else {
            ActivityLog::success('Redmine', "Sinkronisasi selesai ({$summary}).");
        }

        return [
            'fetched'           => count($issues),
            'deleted'           => $deleted,
            'unmapped'          => $unmapped,
            'close_check_error' => $closeCheckError,
        ];
    }
}
