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

    <div class="row g-4">
        {{-- Card 1: Konfigurasi API & Integrasi --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="h6 fw-bold mb-0"><i class="fa-solid fa-plug text-primary me-2"></i>Konfigurasi API &amp; Integrasi</h3>
                </div>
                <form action="{{ route('settings.integration') }}" method="POST" class="d-flex flex-column flex-grow-1">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="redmine_url" class="form-label fw-medium">Redmine Base URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
                                <input type="url" class="form-control" id="redmine_url" name="redmine_url"
                                       value="{{ old('redmine_url', $settings['redmine_url']) }}"
                                       placeholder="https://redmine.example.com" required>
                            </div>
                        </div>
                        <div class="mb-1">
                            <label for="telegram_token" class="form-label fw-medium">Telegram Bot Token</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-brands fa-telegram"></i></span>
                                <input type="password" class="form-control" id="telegram_token" name="telegram_token"
                                       value="{{ old('telegram_token', $settings['telegram_token']) }}"
                                       placeholder="123456789:ABCdefGhIJKlmNoPQRstuVWxyZ" autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="toggleToken" title="Tampilkan">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Dapatkan token dari @BotFather di Telegram.</div>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-end mt-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Card 2: Pengaturan Scheduler --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="h6 fw-bold mb-0"><i class="fa-solid fa-clock text-primary me-2"></i>Pengaturan Scheduler</h3>
                </div>
                <form action="{{ route('settings.scheduler') }}" method="POST" class="d-flex flex-column flex-grow-1">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="scheduler_time" class="form-label fw-medium">Jam Kirim Notifikasi Otomatis</label>
                            <input type="time" class="form-control" id="scheduler_time" name="scheduler_time"
                                   value="{{ old('scheduler_time', $settings['scheduler_time']) }}" required>
                            <div class="form-text">Cron job mengirim reminder setiap hari pada jam ini (default 16:00).</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="scheduler_active"
                                   name="scheduler_active" value="1" @checked(old('scheduler_active', $settings['scheduler_active']))>
                            <label class="form-check-label fw-medium" for="scheduler_active">
                                Scheduler <span id="schedulerLabel">{{ $settings['scheduler_active'] ? 'Active' : 'Inactive' }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-end mt-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Card 3: System & API Logs --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="h6 fw-bold mb-0"><i class="fa-solid fa-terminal text-primary me-2"></i>System &amp; API Logs</h3>
                    <span class="small text-muted">{{ count($logs) }} catatan terakhir</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Level</th>
                                <th>Sumber</th>
                                <th>Pesan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                @php [$color, $label] = $levelMap[$log['level']] ?? $levelMap['info']; @endphp
                                <tr>
                                    <td class="text-nowrap text-muted">{{ $log['time'] }}</td>
                                    <td>
                                        <span class="badge rounded-pill bg-{{ $color }}-subtle text-{{ $color }}-emphasis border border-{{ $color }}-subtle">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td>{{ $log['source'] }}</td>
                                    <td>{{ $log['message'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada log.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Tampilkan / sembunyikan token
    document.getElementById('toggleToken').addEventListener('click', function () {
        const input = document.getElementById('telegram_token');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        this.innerHTML = show ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
    });

    // Label toggle scheduler
    document.getElementById('scheduler_active').addEventListener('change', function () {
        document.getElementById('schedulerLabel').textContent = this.checked ? 'Active' : 'Inactive';
    });
</script>
@endpush
