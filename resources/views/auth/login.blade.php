<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Redmine Bot Admin</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- FontAwesome 6 --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-color: #0d6efd;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f4f6fb;
            font-size: .925rem;
            min-height: 100vh;
        }

        .login-shell {
            min-height: 100vh;
        }

        /* ---------- Left brand panel ---------- */
        .login-brand {
            position: relative;
            overflow: hidden;
            background: linear-gradient(165deg, #131a3d 0%, #1c2461 45%, #2a1f5e 100%);
            color: #fff;
        }

        .login-brand::before {
            content: '';
            position: absolute;
            top: -90px;
            right: -70px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(13, 110, 253, .45), transparent 70%);
            pointer-events: none;
        }

        .login-brand::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: -60px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(232, 65, 60, .35), transparent 70%);
            pointer-events: none;
        }

        .login-brand .brand-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #b32024, #e8413c);
            box-shadow: 0 8px 20px rgba(232, 65, 60, .35);
        }

        .login-brand .feature-item {
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: .75rem;
        }

        .login-brand .feature-icon {
            width: 38px;
            height: 38px;
            border-radius: .6rem;
            background: rgba(255, 255, 255, .1);
            flex-shrink: 0;
        }

        /* ---------- Right form panel ---------- */
        .login-form-wrap {
            background: #fff;
        }

        .login-card {
            width: 100%;
            max-width: 380px;
        }

        .form-control:focus {
            border-color: var(--brand-color);
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15);
        }

        .input-group-text {
            background: #f8f9fc;
            border-right: 0;
            color: #6c757d;
        }

        .input-group .form-control {
            border-left: 0;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--brand-color);
        }

        .btn-brand {
            background: var(--brand-color);
            border: none;
            font-weight: 600;
            padding: .65rem 1rem;
            box-shadow: 0 6px 16px rgba(13, 110, 253, .3);
        }

        .btn-brand:hover {
            background: #0b5ed7;
        }
    </style>
</head>
<body>

    <div class="login-shell d-flex flex-column flex-lg-row">

        {{-- ================= LEFT: BRAND PANEL ================= --}}
        <div class="login-brand d-none d-lg-flex flex-column justify-content-between p-5" style="width: 46%">
            <div class="d-flex align-items-center gap-2">
                <div class="brand-icon rounded-3 d-flex align-items-center justify-content-center">
                    <i class="fa-solid fa-robot fa-lg"></i>
                </div>
                <div class="lh-sm">
                    <div class="fw-bold fs-5">Redmine Bot</div>
                    <small class="text-white-50">Admin Panel</small>
                </div>
            </div>

            <div>
                <h1 class="fw-bold mb-3" style="font-size: 2rem">
                    Kelola Redmine &amp; Telegram<br>dalam satu dashboard.
                </h1>
                <p class="text-white-50 mb-4">
                    Pantau jadwal deploy, sinkronisasi issue, dan notifikasi tim secara real-time.
                </p>

                <div class="d-flex flex-column gap-2">
                    <div class="feature-item d-flex align-items-center gap-3 p-3">
                        <span class="feature-icon d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-diagram-project"></i>
                        </span>
                        <span class="small text-white-50">Integrasi langsung dengan Redmine API</span>
                    </div>
                    <div class="feature-item d-flex align-items-center gap-3 p-3">
                        <span class="feature-icon d-flex align-items-center justify-content-center">
                            <i class="fa-brands fa-telegram"></i>
                        </span>
                        <span class="small text-white-50">Notifikasi otomatis lewat Telegram Bot</span>
                    </div>
                    <div class="feature-item d-flex align-items-center gap-3 p-3">
                        <span class="feature-icon d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-users-gear"></i>
                        </span>
                        <span class="small text-white-50">User mapping Redmine &amp; Telegram</span>
                    </div>
                </div>
            </div>

            <small class="text-white-50">
                <i class="fa-solid fa-code-branch me-1"></i> v1.0.0 &middot; Laravel {{ app()->version() }}
            </small>
        </div>

        {{-- ================= RIGHT: LOGIN FORM ================= --}}
        <div class="login-form-wrap flex-grow-1 d-flex align-items-center justify-content-center p-4 p-md-5">
            <div class="login-card">

                {{-- Brand mobile (tampil hanya di layar kecil) --}}
                <div class="d-flex d-lg-none align-items-center gap-2 mb-4">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:44px;height:44px;background:linear-gradient(135deg,#b32024,#e8413c)">
                        <i class="fa-solid fa-robot text-white"></i>
                    </div>
                    <div class="lh-sm">
                        <div class="fw-bold">Redmine Bot</div>
                        <small class="text-muted">Admin Panel</small>
                    </div>
                </div>

                <h2 class="h4 fw-bold mb-1">Selamat Datang</h2>
                <p class="text-muted mb-4">Masuk ke akun admin untuk melanjutkan.</p>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium small">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                            <input type="email" id="email" name="email" class="form-control"
                                   placeholder="admin@admin.com" value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium small">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control"
                                   placeholder="••••••••" required>
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword" tabindex="-1">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label small" for="remember">Ingat saya</label>
                        </div>
                        <a href="#" class="small text-decoration-none">Lupa password?</a>
                    </div>

                    <button type="submit" class="btn btn-brand w-100 text-white">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Masuk
                    </button>
                </form>

                <p class="text-center text-muted small mt-4 mb-0">
                    &copy; {{ date('Y') }} Redmine Telegram Bot
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
    </script>
</body>
</html>
