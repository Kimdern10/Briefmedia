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

    @include('snippets.site.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ route('theme.asset', ['css', 'bm-admin.css']) }}?v={{ filemtime(public_path('assets/css/bm-admin.css')) }}">
    @stack('styles')
    @livewireStyles
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

    {{-- data-navigate-once: these run on the first visit only; admin pages after that swap in without a reload (bm-navigate.js) --}}
    <script src="{{ route('theme.asset', ['js', 'bm-navigate.js']) }}?v={{ filemtime(public_path('assets/js/bm-navigate.js')) }}" data-navigate-once></script>
    <script src="{{ route('theme.asset', ['js', 'bm-admin.js']) }}?v={{ filemtime(public_path('assets/js/bm-admin.js')) }}" data-navigate-once></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js" data-navigate-once></script>
    <script src="{{ route('theme.asset', ['js', 'bm-alerts.js']) }}?v={{ filemtime(public_path('assets/js/bm-alerts.js')) }}" data-navigate-once></script>

    @foreach (['success' => 'success', 'message' => 'success', 'info' => 'info', 'warning' => 'warning', 'error' => 'error'] as $flashKey => $flashTone)
        @if (session()->has($flashKey))
            <script>window.adToast(@json(session($flashKey)), @json($flashTone));</script>
            @break
        @endif
    @endforeach

    @livewireScripts
    <script data-navigate-once>
        // Toasts sent by admin Livewire components ($this->toast(...))
        window.addEventListener('admin-toast', (event) => window.adToast(event.detail.message, event.detail.tone || 'success'));
    </script>
    @stack('scripts')
</body>
</html>
