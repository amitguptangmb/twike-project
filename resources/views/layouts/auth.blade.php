<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') | TWIKE</title>

    {{-- From-scratch layout matching Velzon's real login page structure
         (split visual panel + form panel, "Forgot password?" inline with
         the Password label, show/hide toggle) - not a copy of Velzon's
         paid theme files/assets. Kept the app's own indigo brand color
         (already used across the Dashboard/UserList reskin) rather than
         Velzon's demo teal, for visual consistency across the app - say
         the word if you'd rather match the teal exactly.

         Left out on purpose because the app doesn't actually support them
         (adding them would just be decoration, not working UI): the
         social-login icon row, "Don't have an account? Signup", and a
         "Remember me" checkbox. All of those are in the Velzon reference
         screenshot but have no backing feature here. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            --vz-primary: #405189;
            --vz-primary-dark: #34406b;
            --vz-primary-soft: rgba(64,81,137,.1);
            --vz-body-bg: #f3f3f9;
            --vz-border: #e9ebec;
            --vz-radius: .25rem;
        }
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        body {
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--vz-primary) 0%, var(--vz-primary-dark) 55%, #232838 100%);
            background-image:
                radial-gradient(rgba(255,255,255,.08) 1px, transparent 1px),
                linear-gradient(135deg, var(--vz-primary) 0%, var(--vz-primary-dark) 55%, #232838 100%);
            background-size: 22px 22px, 100% 100%;
            font-size: .8125rem;
            padding: 1.5rem;
        }

        .np-auth-card {
            display: flex; max-width: 900px; width: 100%; margin: 0 auto;
            border-radius: 1rem; overflow: hidden; background: #fff;
            box-shadow: 0 20px 50px rgba(0,0,0,.25);
        }

        .np-auth-visual {
            flex: 0 0 42%; position: relative; overflow: hidden; color: #fff;
            background: linear-gradient(160deg, var(--vz-primary) 0%, var(--vz-primary-dark) 100%);
            padding: 2.25rem; display: flex; flex-direction: column;
        }
        .np-auth-visual::before {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.12) 1.5px, transparent 1.5px);
            background-size: 18px 18px;
            opacity: .6;
        }
        .np-auth-visual::after {
            content: ''; position: absolute; width: 260px; height: 260px; border-radius: 50%;
            background: rgba(255,255,255,.06); bottom: -110px; right: -80px;
        }
        .np-auth-logo { position: relative; z-index: 1; font-weight: 700; font-size: 1.4rem; letter-spacing: .3px; }
        .np-auth-tagline { position: relative; z-index: 1; margin-top: auto; }
        .np-auth-tagline i { font-size: 1.75rem; opacity: .85; }
        .np-auth-tagline p { margin: .6rem 0 0; font-size: .8125rem; line-height: 1.6; opacity: .85; max-width: 22rem; }

        .np-auth-form { flex: 1; padding: 2.5rem; display: flex; flex-direction: column; justify-content: center; min-width: 0; }
        .np-auth-form h2 { font-size: 1.4rem; font-weight: 700; color: #212529; margin: 0 0 .25rem; }
        .np-auth-form > p.subtitle { color: #878a99; margin-bottom: 1.5rem; }

        .form-label { font-size: .8125rem; font-weight: 500; color: #495057; }
        .form-control { font-size: .8125rem; border-color: #ced4da; }
        .form-control:focus { border-color: var(--vz-primary); box-shadow: 0 0 0 .15rem var(--vz-primary-soft); }
        .btn-primary { background: var(--vz-primary); border-color: var(--vz-primary); font-weight: 500; }
        .btn-primary:hover, .btn-primary:focus { background: var(--vz-primary-dark); border-color: var(--vz-primary-dark); }
        .btn-light { font-size: .8125rem; }

        .np-pass-wrap { position: relative; }
        .np-pass-wrap input { padding-right: 2.5rem; }
        .np-pass-toggle {
            position: absolute; top: 50%; right: .6rem; transform: translateY(-50%);
            border: none; background: none; color: #878a99; padding: .25rem; line-height: 1;
        }
        .np-pass-toggle:hover { color: #495057; }

        .np-auth-footer { text-align: center; margin-top: 1.25rem; font-size: .75rem; color: rgba(255,255,255,.75); }

        @media (max-width: 767.98px) {
            .np-auth-visual { display: none; }
        }
    </style>
    @stack('head')
</head>
<body>
    <div>
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
