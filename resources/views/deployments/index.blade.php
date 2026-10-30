@extends('layouts.app')

@section('title', 'Jadwal Deploy')

@php
    $statusMap = [
        'test-passed' => ['warning',   'Test Passed', 'fa-vial-circle-check'],
        'deployed'    => ['success',   'Deployed',    'fa-rocket'],
        'closed'      => ['dark',      'Closed',      'fa-lock'],
    ];
@endphp

@section('content')
    {{-- Header halaman --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1">Jadwal Deploy</h2>
            <p class="text-muted mb-0">Pantau tugas Redmine berdasarkan status dan kirim pengingat ke PIC.</p>
        </div>
        <form action="{{ route('deployments.sync') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary" id="btnSync">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Sync Data Redmine
            </button>
        </form>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h3 class="h6 fw-bold mb-0"><i class="fa-solid fa-calendar-check text-primary me-2"></i>Daftar Tugas</h3>

            {{-- Filter status --}}
            <div class="btn-group btn-group-sm" role="group" aria-label="Filter status">
                @foreach ($statusMap as $key => [$color, $label, $icon])
                    <a href="{{ route('deployments.index', ['status' => $key]) }}"
                       class="btn {{ $status === $key ? "btn-$color" : "btn-outline-$color" }}">
                        {{ $label }}
                        <span class="badge text-bg-light ms-1">{{ $counts[$key] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Issue ID</th>
                        <th>Judul Task</th>
                        <th>PIC / Developer</th>
                        <th>Jam Deploy</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deployments as $d)
                        @php [$color, $label, $icon] = $statusMap[$d['status']]; @endphp
                        <tr>
                            <td><span class="fw-semibold text-primary">#{{ $d['issue_id'] }}</span></td>
                            <td>{{ $d['title'] }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-semibold flex-shrink-0">
                                        {{ collect(explode(' ', $d['pic']))->map(fn ($w) => $w[0])->take(2)->join('') }}
                                    </span>
                                    {{ $d['pic'] }}
                                </div>
                            </td>
                            <td class="text-nowrap"><i class="fa-regular fa-clock text-muted me-1"></i>{{ $d['deploy_time'] }}</td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $color }} {{ $color === 'warning' ? 'text-dark' : '' }}">
                                    <i class="fa-solid {{ $icon }} me-1"></i>{{ $label }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <form action="{{ route('deployments.notify', $d['issue_id']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary"
                                            title="Kirim Notifikasi Reminder">
                                        <i class="fa-solid fa-paper-plane"></i>
                                        <span class="d-none d-xl-inline ms-1">Kirim Notifikasi Reminder</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                                Tidak ada tugas dengan status ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white small text-muted">
            Menampilkan {{ count($deployments) }} tugas
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Tampilkan spinner saat sinkronisasi berjalan
    document.getElementById('btnSync').closest('form').addEventListener('submit', function () {
        const btn = document.getElementById('btnSync');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyinkronkan...';
    });
</script>
@endpush
