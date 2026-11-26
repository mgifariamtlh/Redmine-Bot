@extends('layouts.app')

@section('title', 'Settings')

@php
    $levelMap = [
        'success' => ['success', 'Success'],
        'info'    => ['info',    'Info'],
        'warning' => ['warning', 'Warning'],
        'error'   => ['danger',  'Error'],
    ];
@endphp

@push('styles')
<style>
    /* Warna badge level log; !important untuk menimpa aturan badge abu-abu di layout. */
    .badge.log-badge { border: 1px solid transparent !important; }
    .badge.log-success { background: #d9f2e1 !important; color: #14632b !important; border-color: #b6e4c5 !important; }
    .badge.log-info    { background: #dbeafe !important; color: #1d4ed8 !important; border-color: #bfdbfe !important; }
    .badge.log-warning { background: #fef3c7 !important; color: #92600a !important; border-color: #fde68a !important; }
    .badge.log-error   { background: #fde0de !important; color: #b3261e !important; border-color: #f8bcb8 !important; }
</style>
@endpush

@section('content')
    <div class="mb-4">
        <h2 class="h4 fw-bold mb-1">Settings</h2>
        <p class="text-muted mb-0">Atur integrasi Redmine &amp; Telegram, scheduler, serta pantau log sistem.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-4">
            {{-- Card 1: Konfigurasi API & Integrasi --}}
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="h6 fw-bold mb-0"><i class="fa-solid fa-plug text-primary me-2"></i>Konfigurasi API &amp; Integrasi</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="redmine_base_url" class="form-label fw-medium">Redmine Base URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
                                <input type="url" class="form-control" id="redmine_base_url" name="redmine_base_url"
                                       value="{{ old('redmine_base_url', $settings['redmine_base_url']) }}"
                                       placeholder="https://redmine.example.com" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="redmine_api_key" class="form-label fw-medium">Redmine API Key</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                <input type="password" class="form-control" id="redmine_api_key" name="redmine_api_key"
                                       placeholder="{{ $settings['has_api_key'] ? 'Tersimpan — kosongkan jika tidak diubah' : 'Masukkan API Key' }}"
                                       autocomplete="new-password">
                                <button class="btn btn-outline-secondary btn-toggle-secret" type="button" data-target="redmine_api_key" title="Tampilkan">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                API Key akun Redmine (role User) yang dapat melihat task rekan tim. Redmine &rarr; My Account &rarr; API access key.
                            </div>
                        </div>
                        <div class="mb-1">
                            <label for="telegram_bot_token" class="form-label fw-medium">Telegram Bot Token</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-brands fa-telegram"></i></span>
                                <input type="password" class="form-control" id="telegram_bot_token" name="telegram_bot_token"
                                       placeholder="{{ $settings['has_bot_token'] ? 'Tersimpan — kosongkan jika tidak diubah' : '123456789:ABCdefGhIJKlmNoPQRstuVWxyZ' }}"
                                       autocomplete="new-password">
                                <button class="btn btn-outline-secondary btn-toggle-secret" type="button" data-target="telegram_bot_token" title="Tampilkan">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Dapatkan token dari @BotFather di Telegram.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Pengaturan Scheduler --}}
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="h6 fw-bold mb-0"><i class="fa-solid fa-clock text-primary me-2"></i>Pengaturan Scheduler</h3>
                    </div>
                    <div class="card-body">
                        <label for="cron_schedule_time" class="form-label fw-medium">Jam Kirim Notifikasi Otomatis</label>
                        <input type="time" class="form-control" id="cron_schedule_time" name="cron_schedule_time"
                               value="{{ old('cron_schedule_time', $settings['cron_schedule_time']) }}" required>
                        <div class="form-text">Cron job mengirim reminder setiap hari pada jam ini (default 16:00).</div>
                    </div>
                </div>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>

    <div class="row g-4 mt-0">

        {{-- Card 3: System & API Logs --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="h6 fw-bold mb-0"><i class="fa-solid fa-terminal text-primary me-2"></i>System &amp; API Logs</h3>
                    <span class="small text-muted">{{ count($logs) }} catatan terakhir</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 js-datatable" data-order='[[0, "desc"]]'>
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Level</th>
                                <th>Sumber</th>
                                <th>Pengguna</th>
                                <th>Pesan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                @php $label = $levelMap[$log->level][1] ?? 'Info'; @endphp
                                <tr>
                                    <td class="text-nowrap text-muted" data-order="{{ $log->created_at->format('Y-m-d H:i:s') }}-{{ str_pad($log->id, 10, '0', STR_PAD_LEFT) }}">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                    <td>
                                        <span class="badge rounded-pill log-badge log-{{ $log->level }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td>{{ $log->source }}</td>
                                    <td class="text-nowrap">{{ $log->user?->name ?? 'Sistem' }}</td>
                                    <td>{{ $log->message }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Tampilkan / sembunyikan kredensial
    document.querySelectorAll('.btn-toggle-secret').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
        });
    });
</script>
@endpush
