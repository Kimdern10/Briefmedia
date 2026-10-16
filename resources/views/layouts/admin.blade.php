<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@hasSection('title')@yield('title') · @endif BriefMedia Admin</title>

    <script>
        (function () {
            var theme = null;
            try { theme = localStorage.getItem('bm-admin-theme'); } catch (e) {}
            if (!theme) { theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    @if(!empty($globalSeo?->favicon))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $globalSeo->favicon) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bm-admin.css') }}?v={{ filemtime(public_path('assets/css/bm-admin.css')) }}">
    @stack('styles')
</head>

<body class="ad">
    <div class="ad-shell">
        @include('snippets.admin_sidebar')
        <div class="ad-backdrop"></div>

        <div class="ad-main">
            @include('snippets.admin_header')

            <main class="ad-content">
                @if ($errors->any() && !View::hasSection('inline_errors'))
                    <div class="ad-notice ad-notice--error">
                        <i class="bi bi-exclamation-octagon"></i>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            @include('snippets.admin_footer')
        </div>
    </div>

    <!-- Confirmation dialog used by forms with data-confirm -->
    <dialog class="ad-dialog" id="ad-confirm">
        <div class="ad-dialog__body">
            <div class="ad-dialog__icon"><i class="bi bi-exclamation-triangle"></i></div>
            <h3 class="ad-dialog__title">Are you sure?</h3>
            <p class="ad-dialog__text"></p>
        </div>
        <div class="ad-dialog__foot">
            <button type="button" class="ad-btn ad-btn--ghost" data-confirm-cancel>Cancel</button>
            <button type="button" class="ad-btn ad-btn--danger" data-confirm-ok>Confirm</button>
        </div>
    </dialog>

    <script src="{{ asset('assets/js/bm-admin.js') }}?v={{ filemtime(public_path('assets/js/bm-admin.js')) }}"></script>

    @foreach (['success' => 'success', 'message' => 'success', 'info' => 'info', 'warning' => 'warning', 'error' => 'error'] as $flashKey => $flashTone)
        @if (session()->has($flashKey))
            <script>window.adToast(@json(session($flashKey)), @json($flashTone));</script>
            @break
        @endif
    @endforeach

    @stack('scripts')
</body>
</html>
