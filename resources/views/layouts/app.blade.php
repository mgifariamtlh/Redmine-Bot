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
    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-hover: rgba(255, 255, 255, .08);
            --sidebar-text: #aab2c9;
            --brand-color: #0d6efd;
            --body-bg: #f4f6fb;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--body-bg);
            font-size: .925rem;
        }

        /* ---------- Sidebar ---------- */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(165deg, #131a3d 0%, #1c2461 45%, #2a1f5e 100%);
            color: var(--sidebar-text);
            overflow: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: -90px;
            right: -70px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(13, 110, 253, .4), transparent 70%);
            pointer-events: none;
        }

        .sidebar::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: -60px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(232, 65, 60, .3), transparent 70%);
            pointer-events: none;
        }

        .sidebar > * {
            position: relative;
            z-index: 1;
        }

        @media (min-width: 992px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 1030;
            }

            .main-wrapper {
                margin-left: var(--sidebar-width);
            }
        }

        .sidebar .brand {
            height: 64px;
            border-bottom: 1px solid rgba(255, 255, 255, .06);
        }

        .sidebar .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #b32024, #e8413c);
        }

        .sidebar .menu-label {
            font-size: .7rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #6c7590;
        }

        .sidebar .nav-link {
            color: var(--sidebar-text);
            border-radius: .5rem;
            padding: .6rem .9rem;
            margin-bottom: .2rem;
            transition: background .15s, color .15s;
        }

        .sidebar .nav-link i {
            width: 22px;
        }

        .sidebar .nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar .nav-link.active {
            background: var(--brand-color);
            color: #fff;
            box-shadow: 0 4px 12px rgba(13, 110, 253, .35);
        }

        /* ---------- Topbar ---------- */
        .topbar {
            height: 64px;
            background: #fff;
            border-bottom: 1px solid #e6e9f0;
        }

        .avatar {
            width: 36px;
            height: 36px;
            font-size: .85rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            background: currentColor;
            animation: pulse 1.8s infinite;
        }

        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(25, 135, 84, .6); }
            70%  { box-shadow: 0 0 0 6px rgba(25, 135, 84, 0); }
            100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
        }

        /* ---------- Content ---------- */
        .card {
            border: 1px solid #e6e9f0;
            border-radius: .75rem;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .04);
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #eef0f5;
            padding: 1rem 1.25rem;
        }

        .table > :not(caption) > * > * {
            padding: .75rem 1rem;
        }

        .table thead th {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6c757d;
            background: #f8f9fc;
            font-weight: 600;
        }
    </style>
    @stack('styles')
</head>
<body>

    {{-- ================= SIDEBAR ================= --}}
    <aside class="sidebar offcanvas-lg offcanvas-start d-flex flex-column" tabindex="-1" id="sidebar">
        <div class="brand d-flex align-items-center px-3 gap-2">
            <div class="brand-icon rounded-3 d-flex align-items-center justify-content-center text-white">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div class="lh-sm">
                <div class="fw-bold text-white">Redmine Bot</div>
                <small class="text-secondary">Admin Panel</small>
            </div>
            <button type="button" class="btn-close btn-close-white ms-auto d-lg-none"
                    data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup"></button>
        </div>

        <nav class="flex-grow-1 p-3 overflow-auto">
            <div class="menu-label px-2 mb-2">Menu Utama</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-gauge-high"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('users.index') }}"
                       class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users-gear"></i> User Mapping
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('deployments.index') }}"
                       class="nav-link {{ request()->routeIs('deployments.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-check"></i> Jadwal Deploy
                    </a>
                </li>
            </ul>

            <div class="menu-label px-2 mb-2 mt-4">Sistem</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('settings.index') }}"
                       class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-gear"></i> Settings
                    </a>
                </li>
            </ul>
        </nav>

        <div class="p-3 border-top border-secondary border-opacity-25 small text-secondary">
            <i class="fa-solid fa-code-branch me-1"></i> v1.0.0 &middot; Laravel {{ app()->version() }}
        </div>
    </aside>

    {{-- ================= MAIN ================= --}}
    <div class="main-wrapper d-flex flex-column min-vh-100">

        {{-- Topbar --}}
        <header class="topbar sticky-top d-flex align-items-center px-3 px-md-4 gap-3">
            <button class="btn btn-light d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Buka menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <h1 class="h6 fw-bold mb-0 text-truncate">
                <i class="fa-brands fa-telegram text-primary me-1"></i> Redmine Bot Admin
            </h1>

            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="badge rounded-pill text-success bg-success-subtle border border-success-subtle px-3 py-2 d-none d-sm-inline-flex align-items-center gap-2">
                    <span class="status-dot"></span> Bot Status: Active
                </span>

                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center gap-2 text-decoration-none text-dark dropdown-toggle"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-semibold">
                            {{ strtoupper(mb_substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                        </span>
                        <span class="d-none d-md-block lh-sm">
                            <span class="d-block fw-semibold">{{ auth()->user()->name ?? 'Administrator' }}</span>
                            <small class="text-muted">Super Admin</small>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="#"><i class="fa-regular fa-user me-2"></i>Profil</a></li>
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
            </div>
        </header>

        {{-- Konten --}}
        <main class="flex-grow-1 p-3 p-md-4">
            @yield('content')
        </main>

        <footer class="px-4 py-3 small text-muted border-top bg-white">
            &copy; {{ date('Y') }} Redmine Telegram Bot &middot; Dashboard Admin
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
