<?php

namespace App\Services;

use App\Models\Deployment;
use App\Models\Setting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class RedmineService
{
    /** Jumlah issue terbaru (berdasarkan updated_on) yang ditarik per status. */
    private const DEFAULT_LIMIT_PER_STATUS = 50;

    /**
     * Tarik issue berstatus test-passed / deployed / closed dari Redmine.
     *
     * @return list<array{issue_id:int, subject:string, redmine_user_id:?int, pic_name:?string, deploy_time:?string, status:string}>
     */
    public function fetchDeployments(): array
    {
        $client = $this->client();
        $field = (string) config('services.redmine.deploy_time_field');

        $rows = [];
        // Satu request per status, hanya issue terbaru (tanpa pagination) agar sync tetap cepat.
        foreach ($this->resolveStatusIds($client) as $statusId => $status) {
            $json = $this->get($client, '/issues.json', [
                'status_id' => $statusId,
                'limit'     => (int) config('services.redmine.limit_per_status', self::DEFAULT_LIMIT_PER_STATUS),
                'sort'      => 'updated_on:desc',
            ]);

            foreach ($json['issues'] ?? [] as $issue) {
                $rows[] = [
                    'issue_id'        => (int) $issue['id'],
                    'subject'         => (string) $issue['subject'],
                    'redmine_user_id' => isset($issue['assigned_to']['id']) ? (int) $issue['assigned_to']['id'] : null,
                    'pic_name'        => $issue['assigned_to']['name'] ?? null,
                    'deploy_time'     => $this->customFieldValue($issue['custom_fields'] ?? [], $field),
                    'status'          => $status,
                ];
            }
        }

        return $rows;
    }

    /**
     * Petakan ID status Redmine => nama status lokal, dicocokkan lewat nama
     * ("Test Passed", "test_passed", "test-passed" semuanya dianggap sama).
     *
     * @return array<int, string>
     */
    private function resolveStatusIds(PendingRequest $client): array
    {
        $json = $this->get($client, '/issue_statuses.json');

        $ids = [];
        foreach ($json['issue_statuses'] ?? [] as $s) {
            $slug = Str::slug($s['name']);
            if (in_array($slug, Deployment::STATUSES, true)) {
                $ids[(int) $s['id']] = $slug;
            }
        }

        if (! $ids) {
            throw new RuntimeException('Status test-passed / deployed / closed tidak ditemukan di Redmine.');
        }

        return $ids;
    }

    private function customFieldValue(array $fields, string $name): ?string
    {
        foreach ($fields as $f) {
            if (strcasecmp($f['name'] ?? '', $name) === 0) {
                $value = is_array($f['value'] ?? null) ? implode(', ', $f['value']) : ($f['value'] ?? null);

                return filled($value) ? (string) $value : null;
            }
        }

        return null;
    }

    private function client(): PendingRequest
    {
        $baseUrl = rtrim((string) Setting::get('redmine_base_url'), '/');
        $apiKey = Setting::get('redmine_api_key');

        if ($baseUrl === '' || blank($apiKey)) {
            throw new RuntimeException('Redmine Base URL / API Key belum diatur di menu Settings.');
        }

        return Http::baseUrl($baseUrl)
            ->withHeaders(['X-Redmine-Api-Key' => $apiKey])
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout((int) config('services.redmine.timeout', 60));
    }

    private function get(PendingRequest $client, string $path, array $query = []): array
    {
        $response = $client->get($path, $query);

        if ($response->status() === 401) {
            throw new RuntimeException('Redmine menolak API Key (401). Periksa kembali di menu Settings.');
        }

        if ($response->failed()) {
            throw new RuntimeException("Redmine mengembalikan HTTP {$response->status()} untuk {$path}.");
        }

        return $response->json() ?? [];
    }
}
