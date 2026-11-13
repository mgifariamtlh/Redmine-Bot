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
    /**
     * Tarik issue Test Passed / Deployed yang Tanggal Deploy-nya terisi dan
     * >= N bulan lalu, diurutkan Tanggal Deploy terbaru dulu. Satu request saja.
     *
     * @return list<array{issue_id:int, subject:string, redmine_user_id:?int, pic_name:?string, deploy_date:string, deploy_time:?string, status:string}>
     */
    public function fetchDeployments(): array
    {
        $client = $this->client();
        $statuses = $this->resolveStatusIds($client);
        $dateFieldId = $this->resolveDateFieldId($client);

        $since = now()->subMonths((int) config('services.redmine.deploy_months_back', 3))->toDateString();

        // Filter bersarang ("f[]/op[]/v[]") dirakit manual: format array bawaan Guzzle (f[0]=)
        // tidak dibaca Redmine sebagai array.
        $query = http_build_query([
            'f'     => ['status_id', "cf_{$dateFieldId}"],
            'op'    => ['status_id' => '=', "cf_{$dateFieldId}" => '>='],
            'v'     => ['status_id' => array_keys($statuses), "cf_{$dateFieldId}" => [$since]],
            'sort'  => "cf_{$dateFieldId}:desc",
            'limit' => (int) config('services.redmine.limit', 100),
        ], '', '&', PHP_QUERY_RFC3986);
        $query = preg_replace('/%5B\d+%5D/', '%5B%5D', $query); // f[0] => f[]

        $json = $this->get($client, '/issues.json?'.$query);

        $dateField = (string) config('services.redmine.deploy_date_field');
        $timeField = (string) config('services.redmine.deploy_time_field');

        $rows = [];
        foreach ($json['issues'] ?? [] as $issue) {
            $fields = $issue['custom_fields'] ?? [];
            $date = $this->customFieldValue($fields, $dateField);
            $status = $statuses[(int) ($issue['status']['id'] ?? 0)] ?? null;

            // Abaikan tanggal deploy kosong dan status di luar yang diinginkan.
            if (! $date || ! $status) {
                continue;
            }

            $rows[] = [
                'issue_id'        => (int) $issue['id'],
                'subject'         => (string) $issue['subject'],
                'redmine_user_id' => isset($issue['assigned_to']['id']) ? (int) $issue['assigned_to']['id'] : null,
                'pic_name'        => $issue['assigned_to']['name'] ?? null,
                'deploy_date'     => $date,
                'deploy_time'     => $this->customFieldValue($fields, $timeField),
                'status'          => $status,
            ];
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
            throw new RuntimeException('Status Test Passed / Deployed tidak ditemukan di Redmine.');
        }

        return $ids;
    }

    /**
     * Filter custom field di Redmine memakai ID angka (cf_<id>). Urutan pencarian:
     * .env, cache di tabel settings, /custom_fields.json (khusus admin), lalu
     * dibaca dari issue contoh.
     */
    private function resolveDateFieldId(PendingRequest $client): int
    {
        if ($id = config('services.redmine.deploy_date_field_id')) {
            return (int) $id;
        }

        $name = (string) config('services.redmine.deploy_date_field');

        if ($id = Setting::get('redmine_deploy_date_field_id')) {
            return (int) $id;
        }

        // Akun non-admin biasanya ditolak (403); abaikan lalu pakai issue contoh.
        $response = $client->get('/custom_fields.json');
        if ($response->successful()) {
            foreach ($response->json('custom_fields', []) as $f) {
                if (strcasecmp($f['name'] ?? '', $name) === 0 && ($f['customized_type'] ?? '') === 'issue') {
                    return $this->rememberFieldId($f['id']);
                }
            }
        }

        $sample = $this->get($client, '/issues.json?'.http_build_query([
            'status_id' => '*',
            'limit'     => 25,
        ]));
        foreach ($sample['issues'] ?? [] as $issue) {
            foreach ($issue['custom_fields'] ?? [] as $f) {
                if (strcasecmp($f['name'] ?? '', $name) === 0 && isset($f['id'])) {
                    return $this->rememberFieldId($f['id']);
                }
            }
        }

        throw new RuntimeException(
            "ID custom field \"{$name}\" tidak ditemukan otomatis. Isi REDMINE_DEPLOY_DATE_FIELD_ID di .env "
            .'(lihat angka cf_<id> pada URL filter di Redmine).'
        );
    }

    private function rememberFieldId(int|string $id): int
    {
        Setting::set('redmine_deploy_date_field_id', (string) $id);

        return (int) $id;
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

    private function get(PendingRequest $client, string $path): array
    {
        $response = $client->get($path);

        if ($response->status() === 401) {
            throw new RuntimeException('Redmine menolak API Key (401). Periksa kembali di menu Settings.');
        }

        if ($response->failed()) {
            throw new RuntimeException("Redmine mengembalikan HTTP {$response->status()} untuk {$path}.");
        }

        return $response->json() ?? [];
    }
}
