@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $statusOrder = ['Scheduled', 'In Progress', 'Done', 'Failed'];
    $deployCollection = collect($deploys);
    $statusCounts = collect($statusOrder)->mapWithKeys(
        fn ($s) => [$s => $deployCollection->where('status', $s)->count()]
    );
    $maxCount = max($statusCounts->max(), 1);
    $pct = round($stats['tasks_done'] / max($stats['tasks_today'], 1) * 100);
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
    {{-- ================= QUICK STATS ================= --}}
    <div class="row g-3 mb-4 align-items-stretch">
        <div class="col-12 col-xl-3 d-flex flex-column justify-content-center pe-xl-4 mb-2 mb-xl-0">
            <h2 class="h4 page-title mb-2">Quick Stats</h2>
            <p class="text-muted mb-3">Ringkasan aktivitas bot hari ini, {{ now()->translatedFormat('l, d F Y') }}</p>
            <div>
                <button class="btn btn-dark btn-sm"><i class="fa-solid fa-rotate me-1"></i> Sinkronisasi Redmine</button>
            </div>
        </div>

        <div class="col-12 col-xl-9">
            <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-xl-5">
                <div class="col">
                    <div class="stat-tile">
                        <div class="stat-ico"><i class="fa-solid fa-user"></i></div>
                        <div class="stat-val">{{ $stats['total_users'] }}</div>
                        <div class="stat-lbl">User Terhubung</div>
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
                        <div class="stat-lbl">Task Selesai</div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-tile">
                        <div class="stat-ico"><i class="fa-solid fa-diagram-project"></i></div>
                        <div class="stat-val">{{ $stats['redmine'] ? 'Online' : 'Offline' }}</div>
                        <div class="stat-lbl">Redmine API</div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-tile">
                        <div class="stat-ico"><i class="fa-brands fa-telegram"></i></div>
                        <div class="stat-val">{{ $stats['telegram'] ? 'Online' : 'Offline' }}</div>
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
                    <h3 class="mb-0">Statistics</h3>
                    <div class="small text-muted">
                        <span class="me-3"><span class="legend-dot" style="background:#0b0b0b"></span>Jumlah task</span>
                        <span><span class="legend-dot" style="background:#d4d4d4"></span>Maksimum</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-bars">
                        @foreach ($statusCounts as $label => $count)
                            <div class="chart-col">
                                @if ($count > 0)<span class="chart-val">{{ $count }}</span>@endif
                                <div class="chart-pair">
                                    <div class="chart-bar" @style(['height: ' . max($count / $maxCount * 100, 4) . '%'])></div>
                                    <div class="chart-bar light" style="height: 100%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="chart-lbl">
                        @foreach ($statusCounts as $label => $count)
                            <span>{{ $label }}</span>
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
                            <small class="text-muted">Integrasi task</small>
                        </div>
                        <span class="conn-tag {{ $stats['redmine'] ? '' : 'off' }}">{{ $stats['redmine'] ? 'Online' : 'Offline' }}</span>
                    </div>
                    <div class="conn-item">
                        <span class="conn-logo"><i class="fa-brands fa-telegram"></i></span>
                        <div class="lh-sm">
                            <div class="fw-bold">Telegram Bot</div>
                            <small class="text-muted">Notifikasi reminder</small>
                        </div>
                        <span class="conn-tag {{ $stats['telegram'] ? '' : 'off' }}">{{ $stats['telegram'] ? 'Online' : 'Offline' }}</span>
                    </div>
                    <div class="conn-item">
                        <span class="conn-logo"><i class="fa-solid fa-user-plus"></i></span>
                        <div class="lh-sm">
                            <div class="fw-bold">User Baru</div>
                            <small class="text-muted">Minggu ini</small>
                        </div>
                        <span class="conn-tag">+{{ $stats['new_users'] }}</span>
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
                            @foreach ($deploys as $deploy)
                                <tr>
                                    <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                    <td><a href="#" class="fw-semibold text-decoration-none">#{{ $deploy['issue_id'] }}</a></td>
                                    <td>{{ $deploy['task'] }}</td>
                                    <td class="text-nowrap"><i class="fa-regular fa-clock text-muted me-1"></i>{{ $deploy['time'] }}</td>
                                    <td class="text-nowrap">{{ $deploy['pic'] }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ $deploy['status'] === 'Failed' ? 'bg-danger-subtle' : ($deploy['status'] === 'Done' ? 'bg-success' : 'bg-secondary') }}">
                                            {{ $deploy['status'] }}
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
                    <h3 class="mb-0">Log Aktivitas Bot</h3>
                    <span class="badge rounded-pill bg-secondary">Live</span>
                </div>
                <ul class="list-group list-group-flush px-2">
                    @foreach ($logs as $log)
                        <li class="list-group-item d-flex gap-3 py-3">
                            <span class="log-ico"><i class="fa-solid {{ $log['icon'] }} fa-sm"></i></span>
                            <div class="small">
                                <div class="fw-semibold">{{ $log['text'] }}</div>
                                <div class="text-muted">{{ $log['time'] }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
