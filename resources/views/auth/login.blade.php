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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #0b0b0b;
            --ink-soft: #6b6b6b;
            --chip: #f2f2f2;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: .875rem;
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background:
                radial-gradient(1200px 500px at 50% 110%, rgba(0,0,0,.10), transparent 70%),
                linear-gradient(180deg, #d6d6d6 0%, #c4c4c4 100%);
            background-attachment: fixed;
        }

        .login-shell {
            display: flex;
            width: 100%;
            max-width: 1040px;
            min-height: 620px;
            background: #fff;
            border: 3px solid #f6f6f6;
            border-radius: 2rem;
            box-shadow: 0 30px 60px -20px rgba(0, 0, 0, .35);
            overflow: hidden;
        }

        /* ---------- Left brand panel ---------- */
        .login-brand {
            position: relative;
            width: 46%;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: var(--ink);
            background:
                radial-gradient(500px 300px at 0% 0%, #fafafa, transparent 70%),
                linear-gradient(180deg, #e9e9e9 0%, #efefef 100%);
            border-radius: 0 1.5rem 1.5rem 0;
            order: 2;
        }

        .logo { font-size: 1.55rem; font-weight: 800; letter-spacing: -.03em; display: flex; align-items: center; gap: .5rem; }

        .login-brand h1 { font-weight: 800; letter-spacing: -.03em; font-size: 2rem; line-height: 1.2; }

        .feature-item {
            display: flex; align-items: center; gap: .85rem;
            background: #fff; border-radius: 1.25rem; padding: .75rem 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.03);
        }

        .feature-icon {
            width: 38px; height: 38px; border-radius: 50%;
            background: var(--chip); color: #333;
            display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
        }

        .feature-item:first-child .feature-icon { background: #0b0b0b; color: #fff; }

        /* ---------- Right form panel ---------- */
        .login-form-wrap {
            flex: 1;
            display: flex; align-items: center; justify-content: center;
            padding: 2.5rem;
            order: 1;
        }

        .login-card { width: 100%; max-width: 360px; }

        .form-control {
            border-radius: 999px; border: 1px solid #e1e1e1; background: #fafafa; padding: .7rem 1rem;
        }

        .form-control:focus { background: #fff; border-color: #0b0b0b; box-shadow: 0 0 0 .2rem rgba(0,0,0,.08); }

        .input-group-text {
            background: var(--chip); border: 1px solid #e1e1e1; border-right: 0; color: #666;
            border-radius: 999px 0 0 999px; padding-left: 1rem;
        }

        .input-group .form-control { border-left: 0; border-radius: 0 999px 999px 0; }
        .input-group .form-control:not(:last-child) { border-radius: 0; border-right: 0; }

        .input-group .btn-eye {
            border: 1px solid #e1e1e1; border-left: 0; background: #fafafa; color: #666;
            border-radius: 0 999px 999px 0; padding: 0 1rem;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .btn-eye { border-color: #0b0b0b; }

        .form-check-input:checked { background-color: #0b0b0b; border-color: #0b0b0b; }
        .form-check-input:focus { border-color: #0b0b0b; box-shadow: 0 0 0 .2rem rgba(0,0,0,.1); }

        .btn-brand {
            background: linear-gradient(180deg, #3a3a3a 0%, #000 55%);
            color: #fff; border: 0; border-radius: 999px;
            font-weight: 700; padding: .8rem 1rem;
            box-shadow: 0 8px 18px rgba(0, 0, 0, .25);
            transition: transform .15s, box-shadow .15s;
        }

        .btn-brand:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 12px 22px rgba(0,0,0,.3); }

        .alert-error { background: #f4dcda; color: #8f1d17; border-radius: 1rem; border: 0; }

        a { color: var(--ink); }

        @media (max-width: 991.98px) {
            .login-brand { display: none; }
            .login-shell { min-height: 0; max-width: 480px; }
            .login-form-wrap { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

    <div class="login-shell">

        {{-- ================= RIGHT: BRAND PANEL ================= --}}
        <div class="login-brand">
            <div class="logo"><i class="fa-solid fa-layer-group"></i> Redminebot</div>

            <div>
                <h1 class="mb-3">Kelola Redmine &amp; Telegram<br>dalam satu dashboard.</h1>
                <p class="text-muted mb-4" style="color: var(--ink-soft)">
                    Pantau jadwal deploy, sinkronisasi issue, dan notifikasi tim secara real-time.
                </p>

                <div class="d-flex flex-column gap-2">
                    <div class="feature-item">
                        <span class="feature-icon"><i class="fa-solid fa-diagram-project"></i></span>
                        <span class="fw-medium">Integrasi langsung dengan Redmine API</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon"><i class="fa-brands fa-telegram"></i></span>
                        <span class="fw-medium">Notifikasi otomatis lewat Telegram Bot</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon"><i class="fa-solid fa-users"></i></span>
                        <span class="fw-medium">User mapping Redmine &amp; Telegram</span>
                    </div>
                </div>
            </div>

            <small style="color: var(--ink-soft)">
                <i class="fa-solid fa-code-branch me-1"></i> v1.0.0 &middot; Laravel {{ app()->version() }}
            </small>
        </div>

        {{-- ================= LEFT: LOGIN FORM ================= --}}
        <div class="login-form-wrap">
            <div class="login-card">

                <div class="logo mb-5"><i class="fa-solid fa-layer-group"></i> Redminebot</div>

                <h2 class="fw-bold mb-1" style="font-size:1.6rem;letter-spacing:-.02em">Selamat Datang</h2>
                <p class="mb-4" style="color: var(--ink-soft)">Masuk ke akun admin untuk melanjutkan.</p>

                @if ($errors->any())
                    <div class="alert alert-error py-2 small">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                            <input type="email" id="email" name="email" class="form-control"
                                   placeholder="admin@admin.com" value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold small">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control"
                                   placeholder="••••••••" required>
                            <button class="btn btn-eye" type="button" id="togglePassword" tabindex="-1">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label small" for="remember">Ingat saya</label>
                        </div>
                        <a href="#" class="small text-decoration-none fw-semibold">Lupa password?</a>
                    </div>

                    <button type="submit" class="btn btn-brand w-100">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Masuk
                    </button>
                </form>

                <p class="text-center small mt-4 mb-0" style="color: var(--ink-soft)">
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
