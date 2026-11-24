<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · IT SNSac</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --navy:    #1e2f6d;
            --navy-2:  #243b7a;
            --pill:    #0b4fb3;
            --accent:  #4a5fc1;
            --accent-d:#3f53b0;
            --label:   #5f73a8;
            --soft:    #8a97bd;
            --field:   #e5eeff;
            --field-ico:#9aa9cf;
            --danger:  #c0352f;
        }

        * { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: 'Nunito', system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--navy);
            background: linear-gradient(135deg, #e7f1ff 0%, #f8fbff 42%, #e4f2ff 100%);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ---------- Latar halaman ---------- */
        .bg { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }

        .bg-blob {
            position: absolute;
            left: -120px;
            top: -140px;
            width: 560px;
            height: 560px;
            border-radius: 50%;
            background: #8fb0e3;
            opacity: .42;
            filter: blur(90px);
            animation: float-a 22s ease-in-out infinite alternate;
        }

        .bg-circle { position: absolute; border-radius: 50%; }

        .bg-circle.tr {
            right: -110px;
            top: -120px;
            width: 300px;
            height: 300px;
            background: rgba(112, 166, 222, .62);
            animation: float-b 16s ease-in-out infinite alternate;
        }

        .bg-circle.bl {
            left: -100px;
            bottom: -150px;
            width: 300px;
            height: 300px;
            background: rgba(160, 214, 245, .75);
            animation: float-a 19s ease-in-out infinite alternate;
        }

        @keyframes float-a { from { transform: translate(0, 0); } to { transform: translate(26px, 18px); } }
        @keyframes float-b { from { transform: translate(0, 0); } to { transform: translate(-22px, 20px); } }

        /* ---------- Kartu ---------- */
        .auth-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 880px;
            display: grid;
            grid-template-columns: 1.45fr 1fr;
            background: #fff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(70, 110, 170, .22), 0 2px 8px rgba(70, 110, 170, .08);
        }

        /* ---------- Panel kiri ---------- */
        .brand {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 1.7rem 1.9rem 0;
            background: linear-gradient(160deg, #d9ebff 0%, #cfe6ff 48%, #c6eeff 100%);
            overflow: hidden;
            isolation: isolate;
        }

        .brand::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background: radial-gradient(440px 280px at 45% 0%, rgba(255, 255, 255, .78), transparent 72%);
        }

        /* Aurora pastel */
        .aurora { position: absolute; inset: 0; z-index: -1; pointer-events: none; }

        .aurora .ab {
            position: absolute;
            border-radius: 50%;
            filter: blur(52px);
            will-change: transform;
        }

        .aurora .a1 { width: 280px; height: 280px; left: -70px; top: -80px;  background: #9ff0dc; opacity: .60; animation: drift-1 18s ease-in-out infinite alternate; }
        .aurora .a2 { width: 300px; height: 300px; right: -90px; top: 22%;    background: #b9a8ff; opacity: .42; animation: drift-2 22s ease-in-out infinite alternate; }
        .aurora .a3 { width: 340px; height: 340px; left: 18%; bottom: -150px; background: #8fdcff; opacity: .70; animation: drift-1 26s ease-in-out infinite alternate-reverse; }

        .aurora .ribbon {
            position: absolute;
            left: 8%;
            top: -12%;
            width: 46%;
            height: 92%;
            background: linear-gradient(180deg,
                transparent 0%,
                rgba(120, 235, 210, .60) 42%,
                rgba(140, 160, 255, .40) 72%,
                transparent 100%);
            filter: blur(30px);
            transform: skewX(-14deg);
            -webkit-mask-image: linear-gradient(90deg, transparent, #000 35%, #000 65%, transparent);
            mask-image: linear-gradient(90deg, transparent, #000 35%, #000 65%, transparent);
            will-change: transform, opacity;
            animation: sway 13s ease-in-out infinite alternate;
        }

        @keyframes drift-1 { from { transform: translate(0, 0) scale(1); }    to { transform: translate(46px, 36px) scale(1.12); } }
        @keyframes drift-2 { from { transform: translate(0, 0) scale(1); }    to { transform: translate(-42px, 30px) scale(.94); } }
        @keyframes sway {
            0%   { transform: translateX(-6%) skewX(-14deg); opacity: .55; }
            50%  { opacity: 1; }
            100% { transform: translateX(10%) skewX(-8deg);  opacity: .70; }
        }

        .brand-top { display: flex; align-items: center; gap: .7rem; }

        .brand-top img { height: 84px; width: auto; max-width: 110px; object-fit: contain; }

        .brand-name { font-size: .82rem; font-weight: 800; color: var(--navy); }

        .pill {
            align-self: flex-start;
            display: inline-block;
            padding: .28rem .7rem;
            border-radius: 999px;
            background: var(--pill);
            color: #fff;
            font-size: .6rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .brand .pill { margin: .8rem 0 .55rem; }

        .brand h2 {
            margin: 0 0 .6rem;
            font-size: 2.15rem;
            line-height: 1.12;
            font-weight: 900;
            letter-spacing: -.015em;
            color: var(--navy-2);
            max-width: 13ch;
        }

        .brand p {
            margin: 0;
            max-width: 46ch;
            font-size: .76rem;
            line-height: 1.6;
            color: #6f81ad;
        }

        .illus { margin-top: auto; display: flex; justify-content: center; padding-top: .6rem; }
        .illus svg { display: block; width: min(300px, 80%); height: auto; margin-bottom: -2px; }

        /* ---------- Panel kanan ---------- */
        .form-side {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.2rem 2.3rem;
            background: #fff;
        }

        .form-side .pill { margin-bottom: .55rem; }

        .title { margin: 0 0 .3rem; font-size: 1.75rem; font-weight: 900; letter-spacing: -.01em; color: var(--navy); }

        .sub { margin: 0 0 1.3rem; font-size: .74rem; line-height: 1.5; color: var(--soft); }

        .alert-login {
            display: flex;
            gap: .55rem;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding: .6rem .8rem;
            border-radius: 12px;
            background: #fff0ef;
            border: 1px solid #f2c3c0;
            color: #8c2420;
            font-size: .76rem;
            font-weight: 600;
            line-height: 1.4;
        }

        .alert-login ul { list-style: none; margin: 0; padding: 0; }
        .alert-login i  { margin-top: .08rem; }

        .field { margin-bottom: .9rem; }

        .field label { display: block; margin-bottom: .32rem; font-size: .7rem; font-weight: 800; color: var(--label); }

        .inp-wrap { position: relative; }

        .inp-wrap .ico {
            position: absolute;
            left: .9rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: .85rem;
            color: var(--field-ico);
            pointer-events: none;
        }

        .inp {
            width: 100%;
            height: 42px;
            padding: 0 2.6rem 0 2.5rem;
            border: 2px solid transparent;
            border-radius: 13px;
            background: var(--field);
            font-family: inherit;
            font-size: .78rem;
            font-weight: 600;
            color: #243566;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .inp::placeholder { color: #9aa9cf; font-weight: 500; }

        .inp:focus {
            outline: 0;
            background: #eaf1ff;
            border-color: #4f68d8;
            box-shadow: 0 0 0 4px rgba(79, 104, 216, .15);
        }

        .inp.is-invalid { border-color: var(--danger); background: #fff6f5; }

        .toggle-pass {
            position: absolute;
            right: .35rem;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #7f8fb8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .toggle-pass:hover { background: rgba(79, 104, 216, .10); color: var(--accent); }
        .toggle-pass:focus-visible { outline: 2px solid var(--accent); outline-offset: 1px; }

        .hint-caps {
            display: none;
            align-items: center;
            gap: .35rem;
            margin-top: .4rem;
            font-size: .7rem;
            font-weight: 700;
            color: #9a6200;
        }

        .hint-caps.show { display: flex; }

        .row-opts {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: .15rem 0 1rem;
            font-size: .74rem;
            color: #6a7aa5;
        }

        .remember { display: flex; align-items: center; gap: .5rem; }

        .remember input {
            width: 16px;
            height: 16px;
            margin: 0;
            border: 1.5px solid #8f9ec4;
            border-radius: 5px;
            cursor: pointer;
        }

        .remember input:checked { background-color: var(--accent); border-color: var(--accent); }
        .remember input:focus { box-shadow: 0 0 0 3px rgba(79, 104, 216, .2); }
        .remember label { cursor: pointer; user-select: none; }

        .clock { color: var(--soft); font-variant-numeric: tabular-nums; }

        .btn-go {
            width: 100%;
            height: 46px;
            border: 0;
            border-radius: 13px;
            background: var(--accent);
            color: #fff;
            font-family: inherit;
            font-size: .85rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            box-shadow: 0 12px 22px rgba(74, 95, 193, .32);
            transition: background .15s, transform .1s, box-shadow .15s;
        }

        .btn-go:hover { background: var(--accent-d); }
        .btn-go:active { transform: translateY(1px); box-shadow: 0 6px 14px rgba(74, 95, 193, .30); }
        .btn-go:focus-visible { outline: 3px solid rgba(79, 104, 216, .4); outline-offset: 2px; }
        .btn-go:disabled { background: #8c9bdb; box-shadow: none; cursor: progress; }

        .help { margin: 1.1rem 0 0; font-size: .7rem; line-height: 1.5; color: var(--soft); }

        /* ---------- Responsive ---------- */
        @media (max-width: 767.98px) {
            body { align-items: flex-start; padding: 1rem; }
            .auth-card { grid-template-columns: 1fr; border-radius: 22px; }
            .brand { padding: 1.3rem 1.4rem 1.4rem; }
            .brand-top img { height: 56px; }
            .brand .pill { margin-top: .6rem; }
            .brand h2 { font-size: 1.5rem; max-width: none; margin-bottom: 0; }
            .brand p, .illus { display: none; }
            .form-side { padding: 1.6rem 1.4rem 1.7rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .bg-blob, .bg-circle, .aurora .ab, .aurora .ribbon { animation: none !important; }
            * { transition: none !important; }
        }
    </style>
</head>
<body>

<div class="bg" aria-hidden="true">
    <span class="bg-blob"></span>
    <span class="bg-circle tr"></span>
    <span class="bg-circle bl"></span>
</div>

<div class="auth-card">

    {{-- ===== Kiri ===== --}}
    <section class="brand">
        <div class="aurora" aria-hidden="true">
            <span class="ab a1"></span>
            <span class="ab a2"></span>
            <span class="ab a3"></span>
            <span class="ribbon"></span>
        </div>

        <div class="brand-top">
            <img src="{{ asset('itflow/sns-logo.png') }}" alt="Logo SNS">
            <span class="brand-name">IT SNSac</span>
        </div>

        <span class="pill">Portal internal tim IT</span>

        <h2>Workspace Information Technology SNSac</h2>

        <p>Support, programmer, dan team leader mencatat serta memantau pekerjaan di satu tempat, dari daily check sampai deployment.</p>

        <div class="illus" aria-hidden="true">
            <svg viewBox="0 0 400 300" role="img" aria-label="Ilustrasi orang bekerja di depan komputer" xmlns="http://www.w3.org/2000/svg">
  <ellipse cx="212" cy="289" rx="172" ry="7" fill="#8ea6f2" opacity=".30"/>

  <!-- meja -->
  <rect x="66" y="168" width="284" height="8" rx="3" fill="#dbe4ff"/>
  <rect x="340" y="176" width="6" height="110" fill="#c4d1fb"/>

  <!-- laci -->
  <rect x="66" y="176" width="60" height="110" rx="3" fill="#6574ff"/>
  <rect x="72" y="183" width="48" height="27" rx="2" fill="#5565f0"/>
  <rect x="72" y="213" width="48" height="27" rx="2" fill="#5565f0"/>
  <rect x="72" y="243" width="48" height="37" rx="2" fill="#5565f0"/>
  <rect x="88" y="194" width="16" height="3" rx="1.5" fill="#d3dbff"/>
  <rect x="88" y="224" width="16" height="3" rx="1.5" fill="#d3dbff"/>
  <rect x="88" y="258" width="16" height="3" rx="1.5" fill="#d3dbff"/>

  <!-- monitor -->
  <rect x="240" y="150" width="8" height="18" fill="#4d5bdc"/>
  <rect x="224" y="164" width="40" height="4" rx="2" fill="#4d5bdc"/>
  <rect x="190" y="64" width="112" height="88" rx="6" fill="#5865f2"/>
  <rect x="196" y="70" width="100" height="76" rx="3" fill="#f5f8ff"/>
  <rect x="196" y="70" width="100" height="10" rx="3" fill="#e3eaff"/>
  <circle cx="203" cy="75" r="1.8" fill="#9fb2f3"/><circle cx="209" cy="75" r="1.8" fill="#9fb2f3"/><circle cx="215" cy="75" r="1.8" fill="#9fb2f3"/>
  <rect x="204" y="88" width="44" height="3" rx="1.5" fill="#8fa4f2"/>
  <rect x="204" y="96" width="70" height="3" rx="1.5" fill="#c3cff7"/>
  <rect x="212" y="104" width="52" height="3" rx="1.5" fill="#c3cff7"/>
  <rect x="212" y="112" width="62" height="3" rx="1.5" fill="#a8b9f4"/>
  <rect x="204" y="120" width="36" height="3" rx="1.5" fill="#c3cff7"/>
  <rect x="204" y="128" width="76" height="3" rx="1.5" fill="#c3cff7"/>
  <rect x="204" y="136" width="52" height="3" rx="1.5" fill="#a8b9f4"/>
  <rect x="262" y="86" width="26" height="20" rx="2" fill="#bfe9ff"/>

  <!-- laptop -->
  <rect x="312" y="128" width="36" height="34" rx="2.5" fill="#eef2ff" stroke="#5865f2" stroke-width="3"/>
  <rect x="318" y="136" width="18" height="2.5" rx="1.2" fill="#a8b9f4"/>
  <rect x="318" y="142" width="24" height="2.5" rx="1.2" fill="#c3cff7"/>
  <rect x="318" y="148" width="14" height="2.5" rx="1.2" fill="#c3cff7"/>
  <rect x="304" y="161" width="52" height="7" rx="2.5" fill="#5865f2"/>

  <!-- keyboard -->
  <rect x="196" y="164" width="34" height="4" rx="2" fill="#c4d1fb"/>

  <!-- kursi -->
  <rect x="146" y="217" width="6" height="40" fill="#c4d1fb"/>
  <path d="M120 262 H180" stroke="#c4d1fb" stroke-width="5" stroke-linecap="round"/>
  <circle cx="122" cy="269" r="4" fill="#8fa2f5"/><circle cx="150" cy="269" r="4" fill="#8fa2f5"/><circle cx="178" cy="269" r="4" fill="#8fa2f5"/>
  <rect x="104" y="148" width="20" height="72" rx="10" fill="#5f6fff"/>
  <rect x="112" y="205" width="70" height="12" rx="6" fill="#6f7fff"/>

  <!-- orang -->
  <path d="M136 208 L186 208" stroke="#1f2a5e" stroke-width="16" stroke-linecap="round"/>
  <path d="M186 208 L192 260" stroke="#1f2a5e" stroke-width="13" stroke-linecap="round"/>
  <path d="M186 265 H210" stroke="#11193f" stroke-width="9" stroke-linecap="round"/>
  <path d="M128 142 Q128 134 140 134 L158 136 Q168 142 166 158 L162 210 L128 210 Z" fill="#5865f2"/>
  <rect x="149" y="126" width="10" height="12" rx="3" fill="#e3b48e"/>
  <circle cx="153" cy="111" r="15" fill="#1f2a5e"/>
  <circle cx="159" cy="118" r="12.5" fill="#f2c7a3"/>
  <circle cx="151" cy="120" r="3" fill="#e3b48e"/>
  <path d="M148 146 Q170 152 192 164" stroke="#4d5bdc" stroke-width="11" stroke-linecap="round" fill="none"/>
  <circle cx="196" cy="165" r="5" fill="#f2c7a3"/>

  <!-- tempat sampah -->
  <path d="M354 252 H378 L375 286 H357 Z" fill="#e4eaff" stroke="#9fb2f3" stroke-width="2"/>
  <path d="M362 258 V282 M366 258 V282 M370 258 V282" stroke="#9fb2f3" stroke-width="1.5"/>
</svg>
        </div>
    </section>

    {{-- ===== Kanan ===== --}}
    <main class="form-side">

        <span class="pill">Secure Login</span>
        <h1 class="title">Masuk</h1>
        <p class="sub">Gunakan NIP dan password untuk membuka dashboard sesuai role.</p>

        @if ($errors->any())
            <div class="alert-login" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="loginForm" method="post" action="{{ route('login.store') }}" novalidate>
            @csrf

            <div class="field">
                <label for="nip">NIP</label>
                <div class="inp-wrap">
                    <i class="bi bi-person ico"></i>
                    <input
                        id="nip" name="nip" type="text"
                        maxlength="10" autocomplete="username" autofocus
                        value="{{ old('nip') }}"
                        placeholder="10 digit NIP kamu"
                        class="inp @error('nip') is-invalid @enderror"
                        required
                    >
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="inp-wrap">
                    <i class="bi bi-lock ico"></i>
                    <input
                        id="password" name="password" type="password"
                        autocomplete="current-password"
                        placeholder="Password kamu"
                        class="inp @error('password') is-invalid @enderror"
                        required
                    >
                    <button type="button" class="toggle-pass" id="togglePass"
                            aria-label="Tampilkan password" aria-pressed="false">
                        <i class="bi bi-eye" id="togglePassIcon"></i>
                    </button>
                </div>
                <div class="hint-caps" id="capsHint">
                    <i class="bi bi-exclamation-triangle-fill"></i> Caps Lock sedang aktif
                </div>
            </div>

            <div class="row-opts">
                <div class="remember">
                    <input type="checkbox" id="rememberMe" class="form-check-input">
                    <label for="rememberMe">Ingat NIP saya</label>
                </div>
                <span class="clock" id="clock">WIB</span>
            </div>

            <button type="submit" class="btn-go" id="btnLogin">
                <span class="spinner-border spinner-border-sm d-none" id="btnSpinner" aria-hidden="true"></span>
                <span id="btnText">Masuk Dashboard</span>
                <i class="bi bi-arrow-right" id="btnIcon"></i>
            </button>
        </form>

        <p class="help">Lupa password atau belum punya akses? Hubungi admin IT.</p>

    </main>
</div>

<script>
(function () {
    const KEY_NIP = 'itflow_saved_nip';
    const KEY_REM = 'itflow_remember';

    const form      = document.getElementById('loginForm');
    const nipInput  = document.getElementById('nip');
    const passInput = document.getElementById('password');
    const remBox    = document.getElementById('rememberMe');

    // --- Jam WIB ---
    const clock = document.getElementById('clock');
    const fmt = new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Jakarta'
    });
    function tick() { clock.textContent = fmt.format(new Date()).replace(':', '.') + ' WIB'; }
    tick();
    setInterval(tick, 15000);

    // --- Isi ulang NIP yang tersimpan ---
    let savedNip = null, isRemembered = false;
    try {
        savedNip = localStorage.getItem(KEY_NIP);
        isRemembered = localStorage.getItem(KEY_REM) === '1';
    } catch (e) { /* storage diblokir, lanjut tanpa fitur ingat */ }

    if (isRemembered && savedNip) {
        if (!nipInput.value) nipInput.value = savedNip;
        remBox.checked = true;
        passInput.focus();
    }

    // --- Tampil / sembunyikan password ---
    const toggleBtn  = document.getElementById('togglePass');
    const toggleIcon = document.getElementById('togglePassIcon');

    toggleBtn.addEventListener('click', function () {
        const show = passInput.type === 'password';
        passInput.type = show ? 'text' : 'password';
        toggleIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        toggleBtn.setAttribute('aria-pressed', show ? 'true' : 'false');
        toggleBtn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        passInput.focus();
    });

    // --- Peringatan Caps Lock ---
    const capsHint = document.getElementById('capsHint');
    function checkCaps(e) {
        if (typeof e.getModifierState !== 'function') return;
        capsHint.classList.toggle('show', e.getModifierState('CapsLock'));
    }
    passInput.addEventListener('keydown', checkCaps);
    passInput.addEventListener('keyup', checkCaps);
    passInput.addEventListener('blur', function () { capsHint.classList.remove('show'); });

    // --- Simpan / hapus NIP + status loading saat submit ---
    form.addEventListener('submit', function () {
        try {
            if (remBox.checked) {
                localStorage.setItem(KEY_NIP, nipInput.value.trim());
                localStorage.setItem(KEY_REM, '1');
            } else {
                localStorage.removeItem(KEY_NIP);
                localStorage.removeItem(KEY_REM);
            }
        } catch (e) {}

        const btn = document.getElementById('btnLogin');
        document.getElementById('btnSpinner').classList.remove('d-none');
        document.getElementById('btnIcon').classList.add('d-none');
        document.getElementById('btnText').textContent = 'Memproses...';
        // disable setelah event submit selesai, supaya form tetap terkirim
        setTimeout(function () { btn.disabled = true; }, 0);
    });

    // --- Hapus langsung kalau checkbox di-uncheck ---
    remBox.addEventListener('change', function () {
        if (!this.checked) {
            try {
                localStorage.removeItem(KEY_NIP);
                localStorage.removeItem(KEY_REM);
            } catch (e) {}
        }
    });

    // Tombol kembali dari cache browser: reset tombol yang masih "loading"
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            document.getElementById('btnLogin').disabled = false;
            document.getElementById('btnSpinner').classList.add('d-none');
            document.getElementById('btnIcon').classList.remove('d-none');
            document.getElementById('btnText').textContent = 'Masuk Dashboard';
        }
    });
})();
</script>

</body>
</html>