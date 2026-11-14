<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Setting;
use App\Models\TelegramMapping;
use App\Services\RedmineService;
use App\Services\TelegramService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class TelegramBotController extends Controller
{
    public function __construct(
        private RedmineService $redmine,
        private TelegramService $telegram,
    ) {}

    public function handleWebhook(Request $request): JsonResponse
    {
        // Tanpa verifikasi, siapa pun bisa memalsukan chat_id orang lain. Secret dibuat
        // oleh `php artisan telegram:set-webhook` dan dikirim Telegram di setiap request.
        $secret = Setting::get('telegram_webhook_secret');
        $given = (string) $request->header('X-Telegram-Bot-Api-Secret-Token');
        abort_if(blank($secret) || ! hash_equals($secret, $given), 403);

        // Telegram mengirim ulang update jika balasan lambat; abaikan update yang sudah diproses.
        $updateId = $request->input('update_id');
        if ($updateId !== null && ! Cache::add("telegram:update:{$updateId}", true, now()->addHour())) {
            return $this->ok();
        }

        $chatId = data_get($request->input('message'), 'chat.id');
        $text = trim((string) data_get($request->input('message'), 'text', ''));
        if ($chatId === null || $text === '') {
            return $this->ok(); // bukan pesan teks (stiker, edit, dll.)
        }

        $mapping = TelegramMapping::where('chat_id', (string) $chatId)->first();

        if (! $mapping || ! $mapping->is_active) {
            $this->sendMessage($chatId, "⛔ Akun Anda belum terdaftar atau tidak aktif.\n"
                ."Berikan Chat ID Anda ke admin: `{$chatId}`");

            return $this->ok();
        }

        $this->sendMessage($chatId, $this->reply($this->normalize($text), $mapping));

        return $this->ok();
    }

    /** "/Jadwal_Deploy@NamaBot" => "/jadwal deploy" */
    private function normalize(string $text): string
    {
        return Str::of($text)->before("\n")->lower()
            ->replaceMatches('/^(\/\w+)@\w+/', '$1')
            ->replace('_', ' ')->squish()->toString();
    }

    private function reply(string $command, TelegramMapping $mapping): string
    {
        try {
            return match ($command) {
                '/task', '/mytask'           => $this->myTasks($mapping),
                '/overdue'                   => $this->overdue($mapping),
                '/jadwal deploy', '/jadwal'  => $this->deploySchedule($mapping),
                default                      => $this->help($mapping),
            };
        } catch (RuntimeException|ConnectionException $e) {
            // Detail error hanya ke log; pengguna cukup tahu Redmine sedang bermasalah.
            Log::warning('Telegram bot: gagal mengambil data Redmine', ['error' => $e->getMessage()]);

            return '⚠️ Redmine tidak dapat dihubungi saat ini. Coba lagi beberapa saat lagi.';
        }
    }

    private function help(TelegramMapping $mapping): string
    {
        return "👋 Halo *{$this->md($mapping->name)}*!\n\n"
            ."Perintah yang tersedia:\n"
            ."/task atau /mytask - task Anda berstatus New & In Progress\n"
            ."/overdue - task Anda yang melewati due date\n"
            ."/jadwal deploy - jadwal deploy Anda yang berstatus Test Passed\n"
            .'/help - tampilkan bantuan ini';
    }

    private function myTasks(TelegramMapping $mapping): string
    {
        $result = $this->redmine->fetchActiveTasks($this->redmineUserId($mapping));

        return $this->formatTasks('📋 *Task Anda (New & In Progress)*', $result, 'Tidak ada task New / In Progress.');
    }

    private function overdue(TelegramMapping $mapping): string
    {
        $result = $this->redmine->fetchOverdueTasks($this->redmineUserId($mapping));

        return $this->formatTasks('⏰ *Task Overdue*', $result, '🎉 Tidak ada task yang overdue.');
    }

    private function deploySchedule(TelegramMapping $mapping): string
    {
        $deployments = Deployment::where('status', 'test-passed')
            ->where('redmine_user_id', $mapping->redmine_user_id)
            ->where(fn ($q) => $q->whereNotNull('deploy_date')->orWhereNotNull('deploy_time'))
            ->orderBy('deploy_date')->orderBy('deploy_time')
            ->limit(20)->get();

        if ($deployments->isEmpty()) {
            return '📅 Belum ada jadwal deploy (Test Passed) untuk Anda.';
        }

        $lines = $deployments->map(function (Deployment $d) {
            $when = trim($d->deploy_date?->translatedFormat('d M Y').' '.$d->deploy_time);

            return "• {$this->issueLink($d->issue_id)} {$this->md($this->shorten($d->subject))}\n   🗓 {$when}";
        });

        return "🚀 *Jadwal Deploy Anda*\n\n".$lines->join("\n");
    }

    /**
     * @param  array{total:int, issues:list<array{id:int, subject:string, status:string, due_date:?string}>}  $result
     */
    private function formatTasks(string $title, array $result, string $empty): string
    {
        if (! $result['issues']) {
            return $empty;
        }

        $lines = collect($result['issues'])->map(function ($i) {
            $due = $i['due_date'] ? ' · due '.date('d M Y', strtotime($i['due_date'])) : '';

            return "• {$this->issueLink($i['id'])} {$this->md($this->shorten($i['subject']))}\n   _{$this->md($i['status'])}_{$due}";
        });

        $more = $result['total'] - count($result['issues']);
        $footer = $more > 0 ? "\n\n_…dan {$more} task lainnya di Redmine._" : '';

        return "{$title}\n\n".$lines->join("\n").$footer;
    }

    private function redmineUserId(TelegramMapping $mapping): int
    {
        if (! ctype_digit((string) $mapping->redmine_user_id)) {
            throw new RuntimeException("Redmine User ID tidak valid: {$mapping->redmine_user_id}");
        }

        return (int) $mapping->redmine_user_id;
    }

    private function issueLink(int $issueId): string
    {
        $base = rtrim((string) Setting::get('redmine_base_url'), '/');

        return "[#{$issueId}]({$base}/issues/{$issueId})";
    }

    private function shorten(string $text): string
    {
        return Str::limit($text, 80);
    }

    /** Escape karakter khusus Markdown (legacy) Telegram. */
    private function md(string $text): string
    {
        return preg_replace('/([_*`\[])/', '\\\\$1', $text);
    }

    private function sendMessage(int|string $chatId, string $text): void
    {
        try {
            $this->telegram->sendMessage((string) $chatId, $text, 'Markdown');
        } catch (RuntimeException|ConnectionException $e) {
            // Tetap balas 200 ke Telegram agar update tidak dikirim ulang terus-menerus.
            Log::warning('Telegram bot: gagal mengirim balasan', ['chat_id' => $chatId, 'error' => $e->getMessage()]);
        }
    }

    private function ok(): JsonResponse
    {
        return response()->json(['ok' => true]);
    }
}
