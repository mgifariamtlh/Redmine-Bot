@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $maxCount = max($week->max('total'), 1);
    $pct = (int) round($stats['tasks_done'] / max($stats['tasks_today'], 1) * 100);
    $statusLabel = ['test-passed' => 'Test Passed', 'deployed' => 'Deployed'];
    $logStyle = [
        'success' => 'fa-circle-check',
        'info'    => 'fa-circle-info',
        'warning' => 'fa-triangle-exclamation',
        'error'   => 'fa-circle-xmark',
    ];
@endphp

@push('styles')
<style>
    .chart-bars { display: flex; align-items: flex-end; gap: 1.25rem; height: 190px; padding: 0 .5rem; border-bottom: 1px dashed #d9d9d9; }
    .chart-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; position: relative; }
    .chart-bar { width: 14px; border-radius: 999px; background: #0b0b0b; min-height: 8px; }
    .chart-bar.light { background: #d4d4d4; }
    .chart-pair { display: flex; align-items: flex-end; gap: 6px; height: 100%; }
    .chart-val { font-size: .72rem; font-weight: 700; background: #0b0b0b; color: #fff; border-radius: 999px; padding: .1rem .55rem; margin-bottom: .35rem; }
    .chart-lbl { display: flex; gap: 1.25rem; padding: .6rem .5rem 0; }
    .chart-lbl span { flex: 1; text-align: center; font-size: .75rem; color: var(--ink-soft); }
    .legend-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: .3rem; }

    .conn-item { display: flex; align-items: center; gap: .85rem; padding: .7rem 0; }
    .conn-logo { width: 44px; height: 44px; border-radius: 50%; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.1); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; }
    .conn-tag { margin-left: auto; border: 1px solid #e0e0e0; border-radius: 999px; padding: .3rem .85rem; font-size: .75rem; font-weight: 600; background: #fafafa; }
    .conn-tag.off { background: #f4dcda; border-color: #f4dcda; color: #8f1d17; }

    .log-ico { width: 34px; height: 34px; border-radius: 50%; background: #f2f2f2; color: #333; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
</style>
@endpush

@section('content')
    @foreach (['success' => 'alert-success', 'error' => 'alert-danger'] as $flash => $cls)
        @if (session($flash))
            <div class="alert {{ $cls }} alert-dismissible fade show" role="alert">
                {{ session($flash) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif
    @endforeach

    {{-- ================= QUICK STATS ================= --}}
    <div class="row g-3 mb-4 align-items-stretch">
        <div class="col-12 col-xl-3 d-flex flex-column justify-content-center pe-xl-4 mb-2 mb-xl-0">
            <h2 class="h4 page-title mb-2">Quick Stats</h2>
            <p class="text-muted mb-3">Ringkasan aktivitas bot hari ini, {{ now()->translatedFormat('l, d F Y') }}</p>
            <form action="{{ route('deployments.sync') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-dark btn-sm"><i class="fa-solid fa-rotate me-1"></i> Sinkronisasi Redmine</button>
            </form>
            <small class="text-muted d-block mt-2">
                Sync terakhir: {{ $stats['last_sync'] ? \Illuminate\Support\Carbon::parse($stats['last_sync'])->diffForHumans() : 'belum pernah' }}
            </small>
        </div>

        <div class="col-12 col-xl-9">
            <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-xl-5">
                <div class="col">
                    <div class="stat-tile">
                        <div class="stat-ico"><i class="fa-solid fa-user"></i></div>
                        <div class="stat-val">{{ $stats['active_users'] }}<small class="text-muted fw-semibold fs-6"> / {{ $stats['total_users'] }}</small></div>
                        <div class="stat-lbl">User Aktif</div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-tile dark">
                        <div class="stat-ico"><i class="fa-solid fa-briefcase"></i></div>
                        <div class="stat-val">{{ $stats['tasks_today'] }}</div>
                        <div class="stat-lbl">Task Hari Ini</div>
                        <a href="{{ route('deployments.index') }}" class="stat-arrow text-dark"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-tile">
                        <div class="stat-ico"><i class="fa-solid fa-list-check"></i></div>
                        <div class="stat-val">{{ $stats['tasks_done'] }}<small class="text-muted fw-semibold fs-6"> / {{ $stats['tasks_today'] }}</small></div>
                        <div class="stat-lbl">Sudah Deployed</div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-tile">
                        <div class="stat-ico"><i class="fa-solid fa-diagram-project"></i></div>
                        <div class="stat-val">{{ $stats['redmine'] ? 'Siap' : 'Belum diatur' }}</div>
                        <div class="stat-lbl">Redmine API</div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-tile">
                        <div class="stat-ico"><i class="fa-brands fa-telegram"></i></div>
                        <div class="stat-val">{{ $stats['telegram'] ? 'Siap' : 'Belum diatur' }}</div>
                        <div class="stat-lbl">Telegram Bot</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= STATISTIK + KONEKSI ================= --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h3 class="mb-0">Jadwal Deploy Minggu Ini</h3>
                    <div class="small text-muted">
                        <span class="me-3"><span class="legend-dot" style="background:#0b0b0b"></span>Total jadwal</span>
                        <span><span class="legend-dot" style="background:#d4d4d4"></span>Sudah deployed</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-bars">
                        @foreach ($week as $day)
                            <div class="chart-col">
                                @if ($day['total'] > 0)<span class="chart-val">{{ $day['total'] }}</span>@endif
                                <div class="chart-pair">
                                    <div class="chart-bar" @style(['height: ' . max($day['total'] / $maxCount * 100, 4) . '%'])></div>
                                    <div class="chart-bar light" @style(['height: ' . max($day['deployed'] / $maxCount * 100, 4) . '%'])></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="chart-lbl">
                        @foreach ($week as $day)
                            <span @class(['fw-bold text-dark' => $day['today']])>{{ $day['label'] }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Status Koneksi</h3>
                    <i class="fa-regular fa-circle-dot text-muted"></i>
                </div>
                <div class="card-body pt-2">
                    <div class="conn-item">
                        <span class="conn-logo"><i class="fa-solid fa-diagram-project"></i></span>
                        <div class="lh-sm">
                            <div class="fw-bold">Redmine API</div>
                            <small class="text-muted">API Key {{ $stats['redmine'] ? 'tersimpan' : 'belum diisi' }}</small>
                        </div>
                        <span class="conn-tag {{ $stats['redmine'] ? '' : 'off' }}">{{ $stats['redmine'] ? 'Siap' : 'Belum diatur' }}</span>
                    </div>
                    <div class="conn-item">
                        <span class="conn-logo"><i class="fa-brands fa-telegram"></i></span>
                        <div class="lh-sm">
                            <div class="fw-bold">Telegram Bot</div>
                            <small class="text-muted">Token {{ $stats['telegram'] ? 'tersimpan' : 'belum diisi' }}</small>
                        </div>
                        <span class="conn-tag {{ $stats['telegram'] ? '' : 'off' }}">{{ $stats['telegram'] ? 'Siap' : 'Belum diatur' }}</span>
                    </div>
                    <div class="conn-item">
                        <span class="conn-logo"><i class="fa-solid fa-user-plus"></i></span>
                        <div class="lh-sm">
                            <div class="fw-bold">User Baru</div>
                            <small class="text-muted">Mapping minggu ini</small>
                        </div>
                        <span class="conn-tag">+{{ $stats['new_users'] }}</span>
                    </div>
                    <div class="conn-item">
                        <span class="conn-logo"><i class="fa-solid fa-hourglass-half"></i></span>
                        <div class="lh-sm">
                            <div class="fw-bold">Belum Deploy</div>
                            <small class="text-muted">Status Test Passed</small>
                        </div>
                        <span class="conn-tag">{{ $stats['pending_total'] }}</span>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Progress task hari ini</span><strong>{{ $pct }}%</strong>
                        </div>
                        <div class="progress" style="height:8px;background:#ececec" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" @style(['width: ' . $pct . '%', 'background:#0b0b0b', 'border-radius:999px'])></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= JADWAL DEPLOY + LOG ================= --}}
    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h3 class="mb-0">Jadwal Deploy Hari Ini</h3>
                    <a href="{{ route('deployments.index') }}" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
                </div>
                <div class="table-responsive pb-3">
                    <table class="table align-middle mb-0 js-datatable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:60px">No</th>
                                <th>Issue ID</th>
                                <th>Nama Task</th>
                                <th>Jam Deploy</th>
                                <th>PIC</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deploysToday as $deploy)
                                <tr>
                                    <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                    <td><a href="{{ $deploy->url }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-none">#{{ $deploy->issue_id }}</a></td>
                                    <td>{{ $deploy->subject }}</td>
                                    <td class="text-nowrap"><i class="fa-regular fa-clock text-muted me-1"></i>{{ $deploy->deploy_time ?: '-' }}</td>
                                    <td class="text-nowrap">{{ $deploy->mapping?->name ?? $deploy->pic_name ?? '-' }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ $deploy->status === 'deployed' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $statusLabel[$deploy->status] ?? $deploy->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Log Aktivitas</h3>
                    <a href="{{ route('settings.index') }}" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
                </div>
                <ul class="list-group list-group-flush px-2">
                    @forelse ($logs as $log)
                        <li class="list-group-item d-flex gap-3 py-3">
                            <span class="log-ico"><i class="fa-solid {{ $logStyle[$log->level] ?? 'fa-circle-info' }} fa-sm"></i></span>
                            <div class="small">
                                <div class="fw-semibold">{{ $log->message }}</div>
                                <div class="text-muted">{{ $log->source }} &middot; {{ $log->created_at->diffForHumans() }}</div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-4">Belum ada aktivitas.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
