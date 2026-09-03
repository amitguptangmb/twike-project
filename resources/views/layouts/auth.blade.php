<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') | TWIKE</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            --vz-primary: #000;
            --vz-primary-dark: #000;
            --vz-primary-soft: rgba(0,0,0,.8);
            --vz-body-bg: #000;
            --vz-border: #fff;
            --vz-radius: .375rem;
        }
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        body {
            min-height: 100vh; display: flex;
            align-items: center; justify-content: center;
            background: #000;
            background-image: none;
            background-attachment: fixed;
            font-size: .8125rem;
            padding: 0;
            margin: 0;
        }

        .np-auth-card {
            display: flex; max-width: 900px; width: 100%;
            border-radius: 1rem; overflow: hidden; background: #0a0a0a;
            box-shadow: 0 0 0 1px rgba(255,255,255,.08), 0 20px 50px rgba(0,0,0,.4);
        }

        .np-auth-visual {
            flex: 0 0 42%; position: relative; overflow: hidden; color: #fff;
            background: linear-gradient(160deg, var(--vz-primary) 0%, var(--vz-primary-dark) 100%);
            padding: 2.25rem; display: flex; flex-direction: column;
        }
        .np-auth-visual::before {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.1) 1px, transparent 1px);
            background-size: 16px 16px;
            opacity: .8;
        }
        .np-auth-visual::after {
            content: ''; position: absolute; width: 260px; height: 260px; border-radius: 50%;
            background: rgba(255,255,255,.04); bottom: -110px; right: -80px;
        }
        .np-auth-logo { position: relative; z-index: 1; font-weight: 700; font-size: 1.4rem; letter-spacing: .3px; }
        .np-auth-tagline { position: relative; z-index: 1; margin-top: auto; }
        .np-auth-tagline i { font-size: 1.75rem; opacity: .85; }
        .np-auth-tagline p { margin: .6rem 0 0; font-size: .8125rem; line-height: 1.6; opacity: .85; max-width: 22rem; }

        .np-auth-form { flex: 1; padding: 2.5rem; display: flex; flex-direction: column; justify-content: center; min-width: 0; background: #000; }
        .np-auth-form h2 { font-size: 1.4rem; font-weight: 700; color: #fff; margin: 0 0 .25rem; }
        .np-auth-form > p.subtitle { color: rgba(255,255,255,.5); margin-bottom: 1.5rem; }

        .form-label { font-size: .8125rem; font-weight: 500; color: rgba(255,255,255,.6); }
        .form-control { font-size: .8125rem; border-color: rgba(255,255,255,.12); background: rgba(255,255,255,.04); color: #fff; }
        .form-control::placeholder { color: rgba(255,255,255,.25); }
        .form-control:focus { border-color: #fff; box-shadow: 0 0 0 .15rem rgba(255,255,255,.1); color: #fff; background: rgba(255,255,255,.06); }

        /* Button reset - use higher specificity to override Bootstrap defaults */
        button.btn,
        a.btn,
        .btn-primary,
        .btn-light,
        .btn-primary:hover,
        .btn-primary:focus,
        .btn-light:hover,
        .btn-light:focus {
            background-image: none;
            text-shadow: none;
        }

        .btn-primary,
        button.btn-primary,
        a.btn-primary {
            background-color: #fff !important;
            border-color: #fff !important;
            color: #000 !important;
            font-weight: 600;
        }
        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: #e0e0e0 !important;
            border-color: #e0e0e0 !important;
            color: #000 !important;
        }
        .btn-light,
        button.btn-light,
        a.btn-light {
            background-color: transparent !important;
            border: 1px solid rgba(255,255,255,.25) !important;
            color: #fff !important;
            font-weight: 500;
        }
        .btn-light:hover,
        .btn-light:focus,
        .btn-light:active {
            background-color: rgba(255,255,255,.08) !important;
            border-color: rgba(255,255,255,.4) !important;
            color: #fff !important;
        }

        /* Kill all default Bootstrap blue link colors - black/white only */
        /* But not on .btn elements (which are styled above) */
        a:not(.btn),
        a:not(.btn):hover,
        a:not(.btn):focus,
        a:not(.btn):visited {
            color: rgba(255,255,255,.6);
            text-decoration: none;
        }
        a:not(.btn):hover { color: #fff; }

        /* Captcha image - force grayscale so it matches black/white theme */
        #captchaImg, img[alt="captcha"] {
            filter: grayscale(100%) brightness(1.3) contrast(1.2);
            -webkit-filter: grayscale(100%) brightness(1.3) contrast(1.2);
            background: rgba(255,255,255,.05);
        }

        hr { border-color: rgba(255,255,255,.08); opacity: 1; }

        /* Alerts */
        .alert { border-radius: .375rem; }
        .alert-success { background: rgba(60,255,120,.08); border-color: rgba(60,255,120,.2); color: #6bff9e; }
        .alert-danger { background: rgba(255,60,60,.08); border-color: rgba(255,60,60,.2); color: #ff6b6b; }

        .np-pass-wrap { position: relative; }
        .np-pass-wrap input { padding-right: 2.5rem; }
        .np-pass-toggle {
            position: absolute; top: 50%; right: .6rem; transform: translateY(-50%);
            border: none; background: none; color: rgba(255,255,255,.4); padding: .25rem; line-height: 1;
        }
        .np-pass-toggle:hover { color: rgba(255,255,255,.7); }

        .np-auth-footer { text-align: center; margin-top: 1.25rem; font-size: .75rem; color: rgba(255,255,255,.4); }

        .np-auth-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 900px;
            padding: 1.5rem;
        }

        @media (max-width: 767.98px) {
            .np-auth-visual { display: none; }
        }
    </style>
    @stack('head')
</head>
<body class="@yield('body-class', '')">
    <div class="np-auth-wrapper">
        <div class="np-auth-card">
            <div class="np-auth-visual">
                <div class="np-auth-logo">TWIKE</div>
                <div class="np-auth-tagline">
                    <i class="ri-shield-check-line"></i>
                    <p>Payments, settlements and merchant management for your network - in one place.</p>
                </div>
            </div>
            <div class="np-auth-form">
                <h2>@yield('heading', 'Welcome Back!')</h2>
                <p class="subtitle">@yield('subtitle', 'Sign in to continue.')</p>

                @if (session('status'))
                    <div class="alert alert-success py-2">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger py-2">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
        <div class="np-auth-footer">&copy; {{ date('Y') }} TWIKE SOFTWARE SOLUTION LLP</div>
    </div>

    <script>
        function npTogglePassword(btn, inputId) {
            var input = document.getElementById(inputId);
            var icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'ri-eye-line';
            } else {
                input.type = 'password';
                icon.className = 'ri-eye-off-line';
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
