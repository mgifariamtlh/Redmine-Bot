@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $statusBadge = [
        'Done'        => 'success',
        'In Progress' => 'primary',
        'Scheduled'   => 'warning',
        'Failed'      => 'danger',
    ];
@endphp

@section('content')
    {{-- Header halaman --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1">Dashboard</h2>
            <p class="text-muted mb-0">Ringkasan aktivitas bot hari ini, {{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <button class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-rotate me-1"></i> Sinkronisasi Redmine
        </button>
    </div>

    {{-- ================= STATUS CARDS ================= --}}
    <div class="row g-3 mb-4">
        {{-- Total User --}}
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:56px;height:56px">
                        <i class="fa-solid fa-users fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total User Terhubung</div>
                        <div class="h3 fw-bold mb-0">{{ $stats['total_users'] }}</div>
                        <small class="text-success"><i class="fa-solid fa-arrow-up"></i> {{ $stats['new_users'] }} user baru minggu ini</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Task Hari Ini --}}
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:56px;height:56px">
                        <i class="fa-solid fa-list-check fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted small">Task Hari Ini</div>
                        <div class="h3 fw-bold mb-1">{{ $stats['tasks_today'] }}</div>
                        @php $pct = round($stats['tasks_done'] / max($stats['tasks_today'], 1) * 100); @endphp
                        <div class="progress" style="height:6px" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-warning" style="width: {{ $pct }}%"></div>
                        </div>
                        <small class="text-muted">{{ $stats['tasks_done'] }} dari {{ $stats['tasks_today'] }} selesai</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Koneksi --}}
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Status Koneksi</div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="fw-medium"><i class="fa-solid fa-diagram-project text-danger me-2"></i>Redmine API</span>
                        @if ($stats['redmine'])
                            <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-circle fa-2xs me-1"></i>Online</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-circle fa-2xs me-1"></i>Offline</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <span class="fw-medium"><i class="fa-brands fa-telegram text-primary me-2"></i>Telegram Bot</span>
                        @if ($stats['telegram'])
                            <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-circle fa-2xs me-1"></i>Online</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-circle fa-2xs me-1"></i>Offline</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- ================= JADWAL DEPLOY ================= --}}
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="h6 fw-bold mb-0"><i class="fa-solid fa-calendar-day text-primary me-2"></i>Jadwal Deploy Hari Ini</h3>
                    <a href="#" class="btn btn-sm btn-light">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
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
                            @forelse ($deploys as $deploy)
                                <tr>
                                    <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                    <td><a href="#" class="fw-semibold text-decoration-none">#{{ $deploy['issue_id'] }}</a></td>
                                    <td>{{ $deploy['task'] }}</td>
                                    <td><i class="fa-regular fa-clock text-muted me-1"></i>{{ $deploy['time'] }}</td>
                                    <td class="text-nowrap">{{ $deploy['pic'] }}</td>
                                    <td>
                                        @php $color = $statusBadge[$deploy['status']] ?? 'secondary'; @endphp
                                        <span class="badge rounded-pill bg-{{ $color }}-subtle text-{{ $color }}-emphasis border border-{{ $color }}-subtle">
                                            {{ $deploy['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fa-regular fa-calendar-xmark fa-2x mb-2 d-block"></i>
                                        Tidak ada jadwal deploy hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= LOG AKTIVITAS ================= --}}
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="h6 fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Log Aktivitas Bot</h3>
                    <span class="badge bg-light text-muted border">Live</span>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach ($logs as $log)
                        <li class="list-group-item d-flex gap-3 py-3">
                            <span class="rounded-circle bg-{{ $log['color'] }}-subtle text-{{ $log['color'] }} d-flex align-items-center justify-content-center flex-shrink-0"
                                  style="width:34px;height:34px">
                                <i class="fa-solid {{ $log['icon'] }} fa-sm"></i>
                            </span>
                            <div class="small">
                                <div>{{ $log['text'] }}</div>
                                <div class="text-muted">{{ $log['time'] }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="card-footer bg-white text-center">
                    <a href="#" class="small text-decoration-none">Lihat semua log <i class="fa-solid fa-arrow-right fa-xs"></i></a>
                </div>
            </div>
        </div>
    </div>
@endsection
