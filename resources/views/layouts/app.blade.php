<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Redmine Bot Admin</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- FontAwesome 6 --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    {{-- DataTables (Bootstrap 5) --}}
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* =====================================================
           Tema monokrom (hitam / putih / abu) ala "Jobgio"
           ===================================================== */
        :root {
            --sidebar-width: 270px;
            --ink: #0b0b0b;
            --ink-soft: #6b6b6b;
            --line: #ececec;
            --chip: #f2f2f2;
            --surface: #ffffff;
            --canvas: #ebebeb;

            /* Override warna Bootstrap -> monokrom */
            --bs-body-font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            --bs-body-color: var(--ink);
            --bs-primary: #0b0b0b;
            --bs-primary-rgb: 11, 11, 11;
            --bs-primary-bg-subtle: #ececec;
            --bs-primary-border-subtle: #d6d6d6;
            --bs-primary-text-emphasis: #0b0b0b;
            --bs-link-color: #0b0b0b;
            --bs-link-color-rgb: 11, 11, 11;
            --bs-link-hover-color: #444;
            --bs-link-hover-color-rgb: 68, 68, 68;
            --bs-secondary-color: #6b6b6b;
            --bs-border-radius: .85rem;
            --bs-border-radius-lg: 1.25rem;
            --bs-border-color: #e4e4e4;
            --bs-success: #1f1f1f;
            --bs-success-rgb: 31, 31, 31;
            --bs-success-bg-subtle: #e9e9e9;
            --bs-success-border-subtle: #d0d0d0;
            --bs-success-text-emphasis: #1f1f1f;
            --bs-warning-bg-subtle: #f1f1f1;
            --bs-warning-border-subtle: #dcdcdc;
            --bs-warning-text-emphasis: #444;
            --bs-info-bg-subtle: #f1f1f1;
            --bs-info-border-subtle: #dcdcdc;
            --bs-info-text-emphasis: #444;
        }

        body {
            font-family: var(--bs-body-font-family);
            font-size: .875rem;
            color: var(--ink);
            min-height: 100vh;
            background:
                radial-gradient(1200px 500px at 50% 110%, rgba(0,0,0,.10), transparent 70%),
                linear-gradient(180deg, #d6d6d6 0%, #c4c4c4 100%);
            background-attachment: fixed;
        }

        .text-muted { color: var(--ink-soft) !important; }
        .text-primary, .text-success { color: var(--ink) !important; }
        .text-warning { color: #8a8a8a !important; }

        /* ---------- Shell ---------- */
        .app-shell {
            display: flex;
            height: 100vh;
            background: var(--surface);
            overflow: hidden;
        }

        /* ---------- Sidebar ---------- */
        .sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0;
            background: var(--surface);
            color: var(--ink);
        }

        .sidebar .brand {
            height: 96px;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .brand-mark {
            width: 34px; height: 34px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: .85rem;
            color: #2b2b2b;
            font-weight: 500;
            border-radius: 999px;
            padding: .35rem .9rem .35rem .35rem;
            margin-bottom: .55rem;
            transition: background .15s, color .15s;
        }

        .sidebar .nav-link .nav-ico {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: var(--chip);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .8rem;
            color: #555;
            flex-shrink: 0;
            transition: background .15s, color .15s;
        }

        .sidebar .nav-link:hover { background: #f6f6f6; color: var(--ink); }

        .sidebar .nav-link.active {
            background: linear-gradient(180deg, #3a3a3a 0%, #000 55%);
            color: #fff;
            box-shadow: 0 8px 18px rgba(0, 0, 0, .25);
        }

        .sidebar .nav-link.active .nav-ico { background: #fff; color: #000; }

        .sidebar .menu-label {
            font-size: .68rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #9a9a9a;
            font-weight: 600;
        }

        .sidebar .user-box { text-align: center; padding: 1rem 1rem 1.5rem; }

        .avatar {
            width: 36px; height: 36px;
            font-size: .8rem;
            background: var(--chip) !important;
            color: var(--ink) !important;
            border: 1px solid #e2e2e2;
        }

        .avatar-lg {
            width: 56px; height: 56px;
            font-size: 1.1rem;
            background: #0b0b0b !important;
            color: #fff !important;
            border: 0;
            position: relative;
        }

        /* ---------- Main area ---------- */
        .app-main {
            flex: 1;
            min-width: 0;
            overflow-y: auto;
            background:
                radial-gradient(600px 300px at 0% 0%, #fafafa, transparent 70%),
                linear-gradient(180deg, #e9e9e9 0%, #efefef 100%);
            padding: 1.25rem 1.75rem 1rem;
        }

        @media (max-width: 991.98px) {
            .app-main { padding: 1rem; }
        }

        .main-top {
            display: flex; align-items: center; gap: .75rem;
            margin-bottom: 1.25rem;
        }

        .pill {
            display: inline-flex; align-items: center; gap: .5rem;
            background: #fff; border: 1px solid var(--line);
            border-radius: 999px; padding: .45rem .9rem;
            font-weight: 600; font-size: .78rem;
        }

        .status-dot {
            width: 8px; height: 8px; border-radius: 50%;
            display: inline-block; background: #0b0b0b;
            animation: pulse 1.8s infinite;
        }

        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(0, 0, 0, .45); }
            70%  { box-shadow: 0 0 0 6px rgba(0, 0, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 0, 0, 0); }
        }

        .page-title { font-weight: 800; letter-spacing: -.02em; }

        /* ---------- Cards ---------- */
        .card {
            background: #fff;
            border: 0;
            border-radius: 1.5rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .03);
        }

        .card-header {
            background: transparent;
            border-bottom: 0;
            padding: 1.35rem 1.5rem .5rem;
        }

        .card-header .h6, .card-header h3 { font-size: 1.15rem !important; font-weight: 700 !important; letter-spacing: -.01em; }
        .card-header h3 i { display: none; }

        .card-body { padding: 1.25rem 1.5rem; }

        .card-footer { background: transparent !important; border-top: 0; padding: .75rem 1.5rem 1.25rem; }

        /* Stat tile (kartu ringkasan) */
        .stat-tile {
            background: #fff;
            border-radius: 2.25rem;
            padding: 1.75rem 1rem 2rem;
            text-align: center;
            height: 100%;
            position: relative;
        }

        .stat-tile .stat-ico {
            width: 48px; height: 48px; margin: 0 auto .85rem;
            border-radius: 50%; background: var(--chip); color: #333;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
        }

        .stat-tile.dark .stat-ico { background: #0b0b0b; color: #fff; }
        .stat-tile .stat-val { font-size: 1.4rem; font-weight: 800; letter-spacing: -.02em; line-height: 1.1; }
        .stat-tile .stat-lbl { font-size: .78rem; color: var(--ink-soft); margin-top: .2rem; }
        .stat-tile .stat-arrow {
            position: absolute; left: 50%; bottom: -14px; transform: translateX(-50%);
            width: 34px; height: 34px; border-radius: 50%; background: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,.08); font-size: .7rem;
        }

        /* ---------- Buttons ---------- */
        .btn { border-radius: 999px; font-weight: 600; padding: .5rem 1.15rem; }
        .btn-sm { padding: .35rem .85rem; }

        .btn-primary, .btn-dark {
            --bs-btn-bg: #0b0b0b; --bs-btn-border-color: #0b0b0b; --bs-btn-color: #fff;
            --bs-btn-hover-bg: #2a2a2a; --bs-btn-hover-border-color: #2a2a2a; --bs-btn-hover-color: #fff;
            --bs-btn-active-bg: #000; --bs-btn-active-border-color: #000;
            --bs-btn-disabled-bg: #0b0b0b; --bs-btn-disabled-border-color: #0b0b0b;
        }

        .btn-outline-primary, .btn-outline-secondary, .btn-outline-success, .btn-outline-warning {
            --bs-btn-color: #0b0b0b; --bs-btn-border-color: #cfcfcf; --bs-btn-bg: #fff;
            --bs-btn-hover-bg: #0b0b0b; --bs-btn-hover-border-color: #0b0b0b; --bs-btn-hover-color: #fff;
            --bs-btn-active-bg: #0b0b0b; --bs-btn-active-border-color: #0b0b0b; --bs-btn-active-color: #fff;
            --bs-btn-disabled-color: #9a9a9a; --bs-btn-disabled-border-color: #e0e0e0;
        }

        .btn-success, .btn-warning, .btn-info {
            --bs-btn-bg: #0b0b0b; --bs-btn-border-color: #0b0b0b; --bs-btn-color: #fff;
            --bs-btn-hover-bg: #2a2a2a; --bs-btn-hover-border-color: #2a2a2a; --bs-btn-hover-color: #fff;
            --bs-btn-active-bg: #000; --bs-btn-active-border-color: #000; --bs-btn-active-color: #fff;
        }

        .btn-outline-danger { --bs-btn-color: #b3261e; --bs-btn-border-color: #e3b7b4; --bs-btn-hover-bg: #b3261e; --bs-btn-hover-border-color: #b3261e; }
        .btn-danger { --bs-btn-bg: #b3261e; --bs-btn-border-color: #b3261e; --bs-btn-hover-bg: #8f1d17; --bs-btn-hover-border-color: #8f1d17; }

        .btn-light { --bs-btn-bg: #f2f2f2; --bs-btn-border-color: #f2f2f2; --bs-btn-hover-bg: #e6e6e6; --bs-btn-hover-border-color: #e6e6e6; }

        /* ---------- Tables ---------- */
        .table { --bs-table-bg: transparent; --bs-table-hover-bg: #f6f6f6; }

        .table > :not(caption) > * > * { padding: .85rem 1rem; border-bottom: 0; }

        .table thead th {
            font-size: .78rem; text-transform: none; letter-spacing: 0;
            color: var(--ink-soft); background: transparent; font-weight: 600;
        }

        .table tbody tr { border-radius: 1rem; }
        .table tbody tr:nth-child(odd) > * { background: #f7f7f7; }
        .table tbody tr > :first-child { border-radius: 1rem 0 0 1rem; }
        .table tbody tr > :last-child { border-radius: 0 1rem 1rem 0; }
        .table tbody tr td[colspan] { background: transparent !important; }
        .table-responsive { padding: 0 1rem; }

        /* ---------- Badges ---------- */
        .badge { font-weight: 600; padding: .45em .85em; }
        .badge.bg-warning, .badge.bg-success, .badge.bg-secondary {
            background: #e6e6e6 !important; color: #1a1a1a !important;
        }
        .badge.bg-success { background: #0b0b0b !important; color: #fff !important; }
        .badge[class*="-subtle"] { background: #e6e6e6 !important; color: #1a1a1a !important; border-color: transparent !important; }
        .badge.bg-danger-subtle { background: #f4dcda !important; color: #8f1d17 !important; }

        /* ---------- Forms ---------- */
        .form-control, .form-select {
            border-radius: .9rem; border-color: #e1e1e1; background-color: #fafafa; padding: .6rem .9rem;
        }
        .form-control:focus, .form-select:focus {
            background: #fff; border-color: #0b0b0b; box-shadow: 0 0 0 .2rem rgba(0, 0, 0, .08);
        }
        .input-group-text { background: #f2f2f2; border-color: #e1e1e1; border-radius: .9rem; color: #666; }
        .input-group > .form-control:not(:first-child), .input-group > .btn:not(:first-child) { border-top-left-radius: 0; border-bottom-left-radius: 0; }
        .input-group > :not(:last-child) { border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important; }
        .input-group > :not(:first-child) { border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important; }
        .input-group > .btn:last-child, .input-group > .form-control:last-child { border-top-right-radius: .9rem !important; border-bottom-right-radius: .9rem !important; }
        .form-check-input:checked { background-color: #0b0b0b; border-color: #0b0b0b; }
        .form-check-input:focus { border-color: #0b0b0b; box-shadow: 0 0 0 .2rem rgba(0, 0, 0, .1); }

        /* ---------- Alerts, modal, dropdown, pagination ---------- */
        .alert { border: 0; border-radius: 1rem; }
        .alert-success { background: #0b0b0b; color: #fff; }
        .alert-success .btn-close { filter: invert(1); }
        .alert-danger { background: #f4dcda; color: #8f1d17; }

        .modal-content { border: 0; border-radius: 1.5rem; }
        .modal-header, .modal-footer { border: 0; }

        .dropdown-menu { border: 0; border-radius: 1rem; box-shadow: 0 12px 30px rgba(0,0,0,.15); padding: .5rem; }
        .dropdown-item { border-radius: .6rem; }
        .dropdown-item:active { background: #0b0b0b; }

        .pagination { --bs-pagination-active-bg: #0b0b0b; --bs-pagination-active-border-color: #0b0b0b; --bs-pagination-color: #0b0b0b; margin-bottom: 0; }
        .page-link { border-radius: 999px !important; margin: 0 2px; border: 0; background: #f2f2f2; }

        .list-group-item { border-color: transparent; background: transparent; }

        code { color: #333; background: #efefef; padding: .1rem .4rem; border-radius: .4rem; }

        /* ---------- DataTables ---------- */
        .dataTables_wrapper { padding: .25rem 0 .5rem; }
        .dataTables_wrapper > .row:first-child { margin-bottom: .75rem; align-items: center; }
        .dataTables_wrapper > .row:last-child { margin-top: 1rem; align-items: center; }
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label { display: flex; align-items: center; gap: .5rem; margin: 0; font-weight: 500; color: var(--ink-soft); white-space: nowrap; }
        .dataTables_wrapper .dataTables_filter { display: flex; justify-content: flex-end; }
        .dataTables_wrapper .dataTables_filter input { width: 220px; margin: 0 !important; border-radius: 999px; }
        .dataTables_wrapper .dataTables_length select { width: auto; border-radius: 999px; margin: 0 !important; }
        .dataTables_wrapper .dataTables_info { padding: 0 !important; color: var(--ink-soft); font-size: .8rem; }
        .dataTables_wrapper .dataTables_paginate { display: flex; justify-content: flex-end; }
        table.dataTable { border-collapse: separate !important; border-spacing: 0 !important; margin: 0 !important; }
        table.dataTable thead > tr > th { border-bottom: 0 !important; }
        table.dataTable thead .sorting, table.dataTable thead .sorting_asc, table.dataTable thead .sorting_desc { cursor: pointer; }
        table.dataTable > tbody > tr > * { box-shadow: none !important; }
        table.dataTable > tbody > tr:nth-child(even) > * { background: transparent; }
        table.dataTable > tbody > tr:nth-child(odd) > * { background: #f7f7f7; }
        table.dataTable td.dataTables_empty { background: transparent !important; text-align: center; color: var(--ink-soft); padding: 2.5rem 1rem; }
        @media (max-width: 767.98px) {
            .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_paginate { justify-content: flex-start; margin-top: .5rem; }
        }

        .app-footer { padding: 1rem .5rem .25rem; color: var(--ink-soft); font-size: .78rem; }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-shell">

    {{-- ================= SIDEBAR ================= --}}
    <aside class="sidebar offcanvas-lg offcanvas-start d-flex flex-column" tabindex="-1" id="sidebar">
        <div class="brand d-flex align-items-center px-4 gap-2">
            <span class="brand-mark"><i class="fa-solid fa-layer-group"></i></span>
            <span>Redminebot</span>
            <button type="button" class="btn-close ms-auto d-lg-none"
                    data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup"></button>
        </div>

        <nav class="flex-grow-1 px-3 overflow-auto">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-ico"><i class="fa-solid fa-house"></i></span> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user-mappings.index') }}"
                       class="nav-link {{ request()->routeIs('user-mappings.*') ? 'active' : '' }}">
                        <span class="nav-ico"><i class="fa-solid fa-users"></i></span> User Mapping
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('deployments.index') }}"
                       class="nav-link {{ request()->routeIs('deployments.*') ? 'active' : '' }}">
                        <span class="nav-ico"><i class="fa-solid fa-briefcase"></i></span> Jadwal Deploy
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('settings.index') }}"
                       class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <span class="nav-ico"><i class="fa-solid fa-gear"></i></span> Settings
                    </a>
                </li>
            </ul>
        </nav>

        {{-- Profil user --}}
        <div class="user-box dropup">
            <a href="#" class="text-decoration-none text-dark d-block" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar avatar-lg rounded-circle d-inline-flex align-items-center justify-content-center fw-bold mb-2">
                    {{ strtoupper(mb_substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                </span>
                <div class="fw-bold">{{ auth()->user()->name ?? 'Administrator' }}</div>
                <small class="text-muted d-block text-truncate">{{ auth()->user()->email ?? 'Super Admin' }}</small>
            </a>
            <ul class="dropdown-menu shadow-sm">
                <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="fa-solid fa-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </aside>

    {{-- ================= MAIN ================= --}}
    <div class="app-main">
        <div class="main-top">
            <button class="btn btn-light d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Buka menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="pill ms-auto">
                <span class="status-dot"></span> Bot Status: Active
            </span>
        </div>

        @yield('content')

        <footer class="app-footer">
            &copy; {{ date('Y') }} Redmine Telegram Bot &middot; Dashboard Admin
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    // Semua tabel dengan class .js-datatable otomatis memakai DataTables:
    // search, row count (jumlah baris per halaman), info, dan pagination.
    $(function () {
        $('table.js-datatable').each(function () {
            $(this).DataTable({
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                autoWidth: false,
                columnDefs: [{ targets: 'no-sort', orderable: false, searchable: false }],
                language: {
                    search: 'Cari:',
                    searchPlaceholder: 'Cari data...',
                    lengthMenu: 'Tampilkan _MENU_ baris',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ data)',
                    zeroRecords: 'Tidak ada data yang cocok.',
                    emptyTable: 'Belum ada data.',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' }
                }
            });
        });
    });
</script>
@stack('scripts')
</body>
</html>
