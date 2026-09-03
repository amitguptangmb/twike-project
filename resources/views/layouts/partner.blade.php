<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Partner Portal') | TWIKE</title>

    {{-- From-scratch layout in Velzon's visual style (same open-source
         stack Velzon itself is built on: Bootstrap 5, Inter, RemixIcon) -
         not a copy of Velzon's paid theme files. If you own a Velzon
         license and want the real markup/assets integrated, upload the
         theme files and this layout can be swapped for the genuine one.
         Kept in parity with layouts/admin.blade.php per standing
         instruction ("Keep Velzon everywhere"). --}}

    {{-- Apply saved theme before first paint, so there's no light-flash
         when a user has dark mode saved. --}}
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('twike-theme');
                if (saved === 'dark') document.documentElement.setAttribute('data-bs-theme', 'dark');
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --vz-sidebar-w: 250px;
            --vz-sidebar-collapsed-w: 70px;
            --vz-primary: #405189;
            --vz-primary-dark: #34406b;
            --vz-primary-soft: rgba(64,81,137,.1);
            --vz-body-bg: #f3f3f9;
            --vz-sidebar-bg: #405189;
            --vz-sidebar-text: rgba(255, 255, 255, .65);
            --vz-sidebar-text-active: #fff;
            --vz-topbar-bg: #fff;
            --vz-border: #e9ebec;
            --vz-radius: .25rem;
        }
        :root[data-bs-theme="dark"] {
            --vz-body-bg: #1a1d21;
            --vz-topbar-bg: #212529;
            --vz-border: #32383e;
        }
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        body { background: var(--vz-body-bg); color: #212529; font-size: .8125rem; }
        :root[data-bs-theme="dark"] body { color: #ced4da; }

        /* Sidebar */
        .vz-sidebar {
            position: fixed; inset: 0 auto 0 0; width: var(--vz-sidebar-w);
            background: var(--vz-sidebar-bg); overflow-y: auto; z-index: 1030;
            transition: width .2s ease;
        }
        .vz-sidebar .logo-box {
            height: 70px; display: flex; align-items: center;
            padding: 0 1.35rem; border-bottom: 1px solid rgba(255,255,255,.06);
            text-decoration: none; overflow: hidden;
        }
        .vz-sidebar .logo-box .logo-text {
            color: #fff; font-weight: 700; font-size: 1.3rem; letter-spacing: .4px;
            text-transform: uppercase; white-space: nowrap;
        }
        .vz-sidebar .menu-title {
            color: rgba(255, 255, 255, .45); text-transform: uppercase; font-size: .6875rem; font-weight: 600;
            letter-spacing: .06em; padding: 1.25rem 1.35rem .35rem; white-space: nowrap; overflow: hidden;
        }
        .vz-sidebar .nav-link {
            color: var(--vz-sidebar-text); padding: .55rem 1.35rem; display: flex; align-items: center;
            gap: .65rem; font-size: .8125rem; font-weight: 500; border-radius: 0; position: relative;
        }
        .vz-sidebar .nav-link i { font-size: 1.05rem; line-height: 1; width: 1.1rem; text-align: center; flex-shrink: 0; color: rgba(255, 255, 255, .5); }
        .vz-sidebar .nav-link:hover { color: var(--vz-sidebar-text-active); }
        .vz-sidebar .nav-link:hover i { color: var(--vz-sidebar-text-active); }
        .vz-sidebar .nav-link.active {
            color: var(--vz-sidebar-text-active); font-weight: 600; background: rgba(255, 255, 255, .1);
        }
        .vz-sidebar .nav-link.active i { color: var(--vz-sidebar-text-active); }
        .vz-sidebar .nav-label { white-space: nowrap; }

        /* Collapsed (icon-only) sidebar - real toggle, not decorative */
        body.vz-collapsed .vz-sidebar { width: var(--vz-sidebar-collapsed-w); }
        body.vz-collapsed .vz-sidebar .logo-box { justify-content: center; padding: 0; }
        body.vz-collapsed .vz-sidebar .logo-text,
        body.vz-collapsed .vz-sidebar .menu-title,
        body.vz-collapsed .vz-sidebar .nav-label { display: none; }
        body.vz-collapsed .vz-sidebar .nav-link { justify-content: center; padding: .65rem 0; }
        body.vz-collapsed .vz-content { margin-left: var(--vz-sidebar-collapsed-w); }

        /* Content shell */
        .vz-content { margin-left: var(--vz-sidebar-w); min-height: 100vh; display: flex; flex-direction: column; transition: margin-left .2s ease; }
        .vz-topbar {
            background: var(--vz-topbar-bg); border-bottom: 1px solid var(--vz-border); padding: 0 1.25rem; height: 70px;
            display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1020;
        }
        .vz-topbar .menu-toggle, .vz-topbar .theme-toggle {
            width: 2.4rem; height: 2.4rem; border-radius: var(--vz-radius); border: none; background: transparent;
            display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: #495057;
        }
        :root[data-bs-theme="dark"] .vz-topbar .menu-toggle,
        :root[data-bs-theme="dark"] .vz-topbar .theme-toggle { color: #adb5bd; }
        .vz-topbar .menu-toggle:hover, .vz-topbar .theme-toggle:hover { background: var(--vz-body-bg); }
        .vz-user-btn {
            display: flex; align-items: center; gap: .6rem; text-decoration: none; color: inherit; padding: .25rem .5rem;
            border-radius: var(--vz-radius);
        }
        .vz-user-btn:hover { background: var(--vz-body-bg); }
        .vz-avatar {
            width: 2.2rem; height: 2.2rem; border-radius: 50%; background: var(--vz-primary-soft); color: var(--vz-primary);
            display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem;
        }
        .vz-user-btn .name { font-size: .8125rem; font-weight: 600; color: #212529; line-height: 1.1; }
        .vz-user-btn .role { font-size: .6875rem; color: #878a99; }
        :root[data-bs-theme="dark"] .vz-user-btn .name { color: #e9ecef; }

        .vz-main { padding: 1.5rem; flex: 1; }
        .vz-page-title { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: .5rem; }
        .vz-page-title h1 { font-size: 1.0625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; margin: 0; }
        .vz-breadcrumb { font-size: .75rem; color: #878a99; margin: 0; }
        .vz-breadcrumb a { color: #878a99; text-decoration: none; }
        .vz-breadcrumb .active { color: #495057; }
        :root[data-bs-theme="dark"] .vz-breadcrumb .active { color: #ced4da; }

        .np-card, .card { border: none; border-radius: var(--vz-radius); box-shadow: 0 1px 2px rgba(56,65,74,.08); }
        .np-card .card-header, .card .card-header { background: var(--vz-topbar-bg); border-bottom: 1px solid var(--vz-border); font-weight: 600; font-size: .85rem; }
        .btn-primary { background: var(--vz-primary); border-color: var(--vz-primary); }
        .btn-primary:hover, .btn-primary:focus { background: var(--vz-primary-dark); border-color: var(--vz-primary-dark); }
        .form-control, .form-select { font-size: .8125rem; border-color: #ced4da; }
        .form-control:focus, .form-select:focus { border-color: var(--vz-primary); box-shadow: 0 0 0 .15rem var(--vz-primary-soft); }
        table.dataTable thead th, .table thead th {
            background: var(--vz-body-bg); color: #495057; font-size: .75rem; text-transform: uppercase;
            letter-spacing: .03em; font-weight: 600; border-bottom-width: 1px;
        }
        :root[data-bs-theme="dark"] table.dataTable thead th,
        :root[data-bs-theme="dark"] .table thead th { color: #ced4da; }
        .badge { font-weight: 500; font-size: .6875rem; }

        /* Velzon-style DataTables chrome (search box, length select, pill
           pagination, soft-colored export buttons) - DataTables' own
           default look is plain, this restyles it to match everything
           else in this app. Same block on layouts/admin.blade.php - keep
           both in sync. */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ced4da; border-radius: var(--vz-radius); font-size: .8125rem;
            padding: .3rem .6rem;
        }
        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: var(--vz-primary); box-shadow: 0 0 0 .15rem var(--vz-primary-soft); outline: none;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing { font-size: .8125rem; color: #495057; }
        :root[data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
        :root[data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
        :root[data-bs-theme="dark"] .dataTables_wrapper .dataTables_info { color: #ced4da; }
        .dataTables_wrapper .dataTables_filter { margin-bottom: .75rem; }
        .dataTables_wrapper .dataTables_filter label { display: flex; align-items: center; gap: .5rem; }

        .dataTables_wrapper .dt-buttons { margin-bottom: .75rem; display: flex; gap: .4rem; flex-wrap: wrap; }
        .dataTables_wrapper .dt-button {
            border-radius: 50rem !important; font-size: .6875rem !important; font-weight: 600 !important;
            padding: .35rem 1rem !important; border: 1px solid var(--vz-border) !important;
            background: var(--vz-topbar-bg) !important; color: #495057 !important; box-shadow: none !important;
        }
        :root[data-bs-theme="dark"] .dataTables_wrapper .dt-button { color: #ced4da !important; }
        .dataTables_wrapper .dt-button:hover {
            background: var(--vz-primary-soft) !important; color: var(--vz-primary) !important; border-color: var(--vz-primary) !important;
        }
        .dataTables_wrapper .dt-button.buttons-csv { color: #0ab39c !important; border-color: rgba(10,179,156,.2) !important; }
        .dataTables_wrapper .dt-button.buttons-csv:hover { background: rgba(10,179,156,.1) !important; border-color: #0ab39c !important; }
        .dataTables_wrapper .dt-button.buttons-excel { color: var(--vz-primary) !important; border-color: var(--vz-primary-soft) !important; }
        .dataTables_wrapper .dt-button.buttons-excel:hover { background: var(--vz-primary-soft) !important; border-color: var(--vz-primary) !important; }
        .dataTables_wrapper .dt-button.buttons-pdf { color: #f06548 !important; border-color: rgba(240,101,72,.2) !important; }
        .dataTables_wrapper .dt-button.buttons-pdf:hover { background: rgba(240,101,72,.1) !important; border-color: #f06548 !important; }
        .dataTables_wrapper .dt-button.buttons-print { color: #f7b84b !important; border-color: rgba(247,184,75,.2) !important; }
        .dataTables_wrapper .dt-button.buttons-print:hover { background: rgba(247,184,75,.1) !important; border-color: #f7b84b !important; }

        table.dataTable { border-collapse: separate !important; border-spacing: 0; }
        table.dataTable tbody tr:hover { background: var(--vz-body-bg); }
        table.dataTable tbody td { font-size: .8125rem; vertical-align: middle; border-color: var(--vz-border) !important; }

        .dataTables_wrapper .dataTables_paginate { margin-top: .75rem; }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 50rem !important; margin: 0 .15rem !important; padding: .3rem .75rem !important;
            border: 1px solid transparent !important; color: #495057 !important; font-size: .75rem; font-weight: 600;
        }
        :root[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button { color: #ced4da !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--vz-primary) !important; color: #fff !important; border-color: var(--vz-primary) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
            background: var(--vz-primary-soft) !important; color: var(--vz-primary) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled { opacity: .4; }

        /* Widget / stat cards (Velzon-style icon-avatar widgets) */
        .avatar-sm { width: 3rem; height: 3rem; }
        .avatar-title {
            align-items: center; display: flex; height: 100%; justify-content: center;
            width: 100%; border-radius: .5rem; font-size: 1.25rem;
        }
        .np-widget .card-body { padding: 1.25rem; }
        .np-widget .widget-label {
            color: #878a99; font-size: .75rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: .03em; margin-bottom: .35rem;
        }
        .np-widget .widget-value { font-size: 1.25rem; font-weight: 700; color: #212529; margin: 0; }
        :root[data-bs-theme="dark"] .np-widget .widget-value { color: #e9ecef; }
        .np-pill-btn { border-radius: 50rem; border: none; font-size: .6875rem; font-weight: 600; padding: .4rem .9rem; opacity: 1 !important; }

        @media (max-width: 991.98px) {
            .vz-sidebar { transform: translateX(-100%); transition: transform .2s ease; }
            .vz-sidebar.show { transform: translateX(0); }
            .vz-content { margin-left: 0; }
            /* On mobile the toggle always shows/hides the full sidebar - icon-only
               collapse doesn't make sense on a narrow screen. */
            body.vz-collapsed .vz-sidebar { width: var(--vz-sidebar-w); }
            body.vz-collapsed .vz-sidebar .logo-text,
            body.vz-collapsed .vz-sidebar .menu-title,
            body.vz-collapsed .vz-sidebar .nav-label { display: block; }
            body.vz-collapsed .vz-content { margin-left: 0; }
        }
    </style>
    @stack('head')
</head>
<body>

    {{-- Sidebar items reflect the 8 sections shown in the live "User" panel
         screenshots (Dashboard, Advance Search, Pay In, Pay Out, Settlement,
         Reports, Setting, My Account) - see USER_PANEL_ANALYSIS.md. The
         original agentMaster.Master nav (User List, Pay In, Pay Out, Change
         Password only) doesn't match those screenshots at all; "User List"
         (userlist.aspx, the real ASP.NET landing page) still works at
         partner.users, it's just not linked here anymore, matching what the
         screenshots actually show. Change Password is reachable from the
         user dropdown and from the My Account page's own tab, same as
         before. --}}
    <nav class="vz-sidebar">
        <a class="logo-box" href="{{ route('partner.dashboard') }}">
            <span class="logo-text">TWIKE</span>
        </a>
        <div class="menu-title">Menu</div>
        <ul class="nav flex-column pb-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.dashboard') ? 'active' : '' }}" href="{{ route('partner.dashboard') }}">
                    <i class="ri-dashboard-2-line"></i> <span class="nav-label">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.advance-search*') ? 'active' : '' }}" href="{{ route('partner.advance-search') }}">
                    <i class="ri-search-line"></i> <span class="nav-label">Advance Search</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.collection') ? 'active' : '' }}" href="{{ route('partner.collection') }}">
                    <i class="ri-money-rupee-circle-line"></i> <span class="nav-label">Pay In</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.transactions*') ? 'active' : '' }}" href="{{ route('partner.transactions') }}">
                    <i class="ri-exchange-line"></i> <span class="nav-label">Pay Out</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.settlement*') ? 'active' : '' }}" href="{{ route('partner.settlement') }}">
                    <i class="ri-bank-line"></i> <span class="nav-label">Settlement</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.reports*') ? 'active' : '' }}" href="{{ route('partner.reports') }}">
                    <i class="ri-file-chart-2-line"></i> <span class="nav-label">Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.setting*') ? 'active' : '' }}" href="{{ route('partner.setting') }}">
                    <i class="ri-settings-3-line"></i> <span class="nav-label">Setting</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.account*') ? 'active' : '' }}" href="{{ route('partner.account') }}">
                    <i class="ri-user-settings-line"></i> <span class="nav-label">My Account</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="vz-content">
        <header class="vz-topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="menu-toggle" type="button" onclick="npToggleSidebar()" title="Toggle sidebar">
                    <i class="ri-menu-line"></i>
                </button>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="theme-toggle" type="button" onclick="npToggleTheme()" title="Toggle dark mode">
                    <i class="ri-moon-line" id="np-theme-icon"></i>
                </button>
                <div class="dropdown">
                    <a class="vz-user-btn dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <span class="vz-avatar">{{ strtoupper(substr(session('partner.name', 'P'), 0, 1)) }}</span>
                        <span class="d-none d-sm-block">
                            <span class="d-block name">{{ session('partner.name', 'Partner') }}</span>
                            <span class="d-block role">Partner</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('partner.account') }}"><i class="ri-user-settings-line me-1"></i> My Account</a></li>
                        <li><a class="dropdown-item" href="{{ route('partner.password.edit') }}"><i class="ri-lock-password-line me-1"></i> Change Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('partner.logout') }}"><i class="ri-logout-box-line me-1"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="vz-main">
            <div class="vz-page-title">
                <h1>@yield('title', 'Dashboard')</h1>
                <nav class="vz-breadcrumb">
                    <a href="{{ route('partner.dashboard') }}">TWIKE</a> / <span class="active">@yield('title', 'Dashboard')</span>
                </nav>
            </div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>

        <footer class="text-center text-muted small py-3">
            &copy; {{ date('Y') }} TWIKE SOFTWARE SOLUTION LLP
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script>
        // Sidebar toggle: icon-only collapse on desktop, slide-over on mobile.
        function npToggleSidebar() {
            if (window.innerWidth < 992) {
                document.querySelector('.vz-sidebar').classList.toggle('show');
            } else {
                document.body.classList.toggle('vz-collapsed');
            }
        }

        // Real dark-mode toggle (Bootstrap 5.3 data-bs-theme), persisted in
        // localStorage. Not decorative - actually switches the theme.
        function npApplyThemeIcon() {
            var icon = document.getElementById('np-theme-icon');
            var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            icon.className = isDark ? 'ri-sun-line' : 'ri-moon-line';
        }
        function npToggleTheme() {
            var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            document.documentElement.setAttribute('data-bs-theme', isDark ? 'light' : 'dark');
            try { localStorage.setItem('twike-theme', isDark ? 'light' : 'dark'); } catch (e) {}
            npApplyThemeIcon();
        }
        npApplyThemeIcon();
    </script>
    @stack('scripts')
</body>
</html>
