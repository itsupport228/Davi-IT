<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · IT SNSac</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2fb;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .login-wrap {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }

        .login-card {
            border: 1px solid #d4dff5;
            border-top: 4px solid #3f67d5;
            border-radius: 14px;
            box-shadow: 0 8px 28px rgba(25, 55, 105, .10), 0 2px 6px rgba(25, 55, 105, .05);
            background: #fff;
        }

        .login-card .card-body {
            padding: 2rem 2rem 1.75rem;
        }

        .login-brand {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-size: 1.1rem;
            font-weight: 800;
            color: #15336d;
            letter-spacing: -.02em;
            text-decoration: none;
            margin-bottom: 1.5rem;
        }

        .login-brand .brand-icon {
            width: 32px;
            height: 32px;
            background: #3f67d5;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: .95rem;
        }

        .login-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f2451;
            margin-bottom: .3rem;
            line-height: 1.25;
        }

        .login-sub {
            font-size: .88rem;
            color: #6c7fa8;
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-size: .84rem;
            font-weight: 600;
            color: #2a3f6f;
            margin-bottom: .3rem;
        }

        .form-control {
            border-color: #c5d2ea;
            border-radius: 8px;
            font-size: .9rem;
            padding: .55rem .75rem;
            background: #fafbfd;
            color: #1a2a4a;
            transition: border-color .15s, box-shadow .15s;
        }

        .form-control:focus {
            border-color: #3f67d5;
            box-shadow: 0 0 0 3px rgba(63, 103, 213, .15);
            background: #fff;
        }

        .form-control::placeholder { color: #a0b0cc; }

        .input-group-text {
            border-color: #c5d2ea;
            background: #f0f4fc;
            color: #6c7fa8;
            border-radius: 8px 0 0 8px;
        }

        .btn-login {
            background: #3f67d5;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            font-size: .9rem;
            padding: .6rem 1rem;
            width: 100%;
            transition: background .15s;
        }

        .btn-login:hover { background: #2f55c7; color: #fff; }

        .login-footer {
            padding: .9rem 2rem;
            background: #f5f8ff;
            border-top: 1px solid #dbe5f3;
            border-radius: 0 0 14px 14px;
            font-size: .78rem;
            color: #8090b5;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-wrap">

    <div class="login-card">
        <div class="card-body">

            {{-- Brand --}}
            <span class="login-brand">
                <img src="{{ asset('itflow/sns-logo.png') }}" alt="IT SNSac" width="34" height="34" style="object-fit:contain;">
                IT SNSac
            </span>

            <h1 class="login-title">Halo, selamat datang 👋</h1>
            <p class="login-sub">Silakan masuk — board kamu ada di dalam.</p>

            {{-- Error --}}
            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-start gap-2 py-2 px-3 mb-3" role="alert" style="font-size:.85rem; border-radius:8px;">
                    <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                    <ul class="mb-0 ps-0" style="list-style:none;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form --}}
            <form method="post" action="{{ route('login.store') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="nip" class="form-label">NIP</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input
                            id="nip" name="nip" type="text" inputmode="numeric"
                            maxlength="10" autocomplete="username"
                            value="{{ old('nip') }}"
                            placeholder="10 digit NIP kamu"
                            class="form-control @error('nip') is-invalid @enderror"
                            style="border-radius: 0 8px 8px 0;"
                            required
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input
                            id="password" name="password" type="password"
                            autocomplete="current-password"
                            placeholder="Password kamu"
                            class="form-control @error('password') is-invalid @enderror"
                            style="border-radius: 0 8px 8px 0;"
                            required
                        >
                    </div>
                </div>

                <div class="mb-4 d-flex align-items-center gap-2">
                    <input type="checkbox" id="rememberMe" class="form-check-input m-0" style="width:16px;height:16px;cursor:pointer;border-color:#c5d2ea;">
                    <label for="rememberMe" class="mb-0" style="font-size:.84rem;color:#4a5a80;cursor:pointer;user-select:none;">
                        Ingat NIP saya di perangkat ini
                    </label>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                </button>
            </form>

            <script>
            (function () {
                const KEY_NIP  = 'itflow_saved_nip';
                const KEY_REM  = 'itflow_remember';
                const nipInput = document.getElementById('nip');
                const remBox   = document.getElementById('rememberMe');

                // Restore saved state on load
                const savedNip = localStorage.getItem(KEY_NIP);
                const isRemembered = localStorage.getItem(KEY_REM) === '1';

                if (isRemembered && savedNip) {
                    // Only pre-fill if old() didn't already set a value (i.e. no validation error)
                    if (!nipInput.value) nipInput.value = savedNip;
                    remBox.checked = true;
                    // Focus password so user just types password and hits enter
                    document.getElementById('password').focus();
                }

                // Save / clear on submit
                nipInput.closest('form').addEventListener('submit', function () {
                    if (remBox.checked) {
                        localStorage.setItem(KEY_NIP, nipInput.value.trim());
                        localStorage.setItem(KEY_REM, '1');
                    } else {
                        localStorage.removeItem(KEY_NIP);
                        localStorage.removeItem(KEY_REM);
                    }
                });

                // Live toggle: uncheck → clear immediately so it doesn't linger
                remBox.addEventListener('change', function () {
                    if (!this.checked) {
                        localStorage.removeItem(KEY_NIP);
                        localStorage.removeItem(KEY_REM);
                    }
                });
            })();
            </script>

        </div>

        <div class="login-footer">
            Portal internal tim IT &mdash; akses hanya untuk anggota tim.
        </div>
    </div>

</div>

</body>
</html>
