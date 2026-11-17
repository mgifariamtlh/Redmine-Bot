<?php

namespace App\Console\Commands;

use App\Models\Deployment;
use App\Models\Setting;
use App\Models\TelegramMapping;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class DeployNotifyPic extends Command
{
    protected $signature = 'deploy:notify-pic {--dry-run : Tampilkan pesan di terminal tanpa mengirim ke Telegram}';

    protected $description = 'Kirim pengingat task Test Passed yang belum di-deploy ke masing-masing PIC via Telegram';

    private const DEFAULT_BASE_URL = 'https://redmine.recis.co.id';

    public function handle(TelegramService $telegram): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $baseUrl = rtrim((string) (Setting::get('redmine_base_url') ?: self::DEFAULT_BASE_URL), '/');

        $groups = Deployment::where('status', 'test-passed')
            ->whereNotNull('deploy_date')
            ->whereNotNull('redmine_user_id')
            ->orderBy('deploy_date')->orderBy('deploy_time')->orderBy('issue_id')
            ->get()
            ->groupBy('redmine_user_id');

        if ($groups->isEmpty()) {
            $this->info('Tidak ada task Test Passed dengan tanggal deploy.');

            return self::SUCCESS;
        }

        $mappings = TelegramMapping::where('is_active', true)
            ->whereNotNull('chat_id')->where('chat_id', '!=', '')
            ->whereIn('redmine_user_id', $groups->keys())
            ->get()->keyBy(fn ($m) => (string) $m->redmine_user_id);

        $sent = $failed = 0;

        foreach ($groups as $userId => $tasks) {
            $mapping = $mappings->get((string) $userId);

            if (! $mapping) {
                $this->warn("Lewati Redmine user {$userId}: tidak ada mapping Telegram aktif ({$tasks->count()} task).");

                continue;
            }

            $message = $this->buildMessage($mapping, $tasks, $baseUrl);

            if ($dryRun) {
                $this->line("--- {$mapping->name} ({$mapping->chat_id}) ---");
                $this->line($message);

                continue;
            }

            try {
                $telegram->sendMessage((string) $mapping->chat_id, $message, 'HTML');
                $sent++;
            } catch (Throwable $e) {
                // Satu PIC gagal tidak boleh menghentikan pengiriman ke PIC lain.
                $failed++;
                $this->error("Gagal kirim ke {$mapping->name}: {$e->getMessage()}");
            }
        }

        $this->info("Selesai. Terkirim: {$sent}, gagal: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** @param  Collection<int, Deployment>  $tasks */
    private function buildMessage(TelegramMapping $mapping, Collection $tasks, string $baseUrl): string
    {
        $lines = $tasks->map(function (Deployment $d) use ($baseUrl) {
            $url = e("{$baseUrl}/issues/{$d->issue_id}");
            $subject = e(Str::limit($d->subject, 100));
            $date = $d->deploy_date->translatedFormat('d M Y');
            $time = filled($d->deploy_time) ? ' · ⏰ '.e($d->deploy_time) : '';

            return "• <a href=\"{$url}\">#{$d->issue_id}</a> {$subject}\n   🗓 {$date}{$time}";
        });

        return '🔔 <b>Pengingat Deploy</b>'."\n"
            .'Halo <b>'.e($mapping->name)."</b>, task berikut sudah <i>Test Passed</i> tetapi belum di-deploy:\n\n"
            .$lines->join("\n\n");
    }
}
