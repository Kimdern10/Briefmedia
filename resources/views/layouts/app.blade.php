<!doctype html>
<html lang="en" data-theme="light">
<head>
    <!-- ================= META ================= -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Apply the saved (or system) theme before paint to avoid a flash -->
    <script>
        (function () {
            var theme = null;
            try { theme = localStorage.getItem('bm-theme'); } catch (e) {}
            if (!theme) { theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6884205395656406"
            crossorigin="anonymous"></script>

    <!-- ================= PAGE SEO OVERRIDE ================= -->
    @yield('meta_tags')

    @if (!View::hasSection('meta_tags'))
        <!-- ================= TITLE ================= -->
        <title>@hasSection('title')@yield('title')@else{{ $globalSeo->default_title ?? config('app.name') }}@endif</title>

        <!-- ================= BASIC SEO ================= -->
        <meta name="description" content="{{ $globalSeo->meta_description ?? '' }}">
        <meta name="keywords" content="{{ $globalSeo->meta_keywords ?? '' }}">
        <meta name="author" content="{{ $globalSeo->author ?? '' }}">
        <meta name="robots" content="{{ $globalSeo->robots_meta ?? 'index, follow' }}">
        <link rel="canonical" href="{{ $globalSeo->canonical_url ?? url()->current() }}">

        <!-- ================= OPEN GRAPH ================= -->
        <meta property="og:title" content="{{ $globalSeo->og_title ?? $globalSeo->default_title ?? config('app.name') }}">
        <meta property="og:description" content="{{ $globalSeo->og_description ?? $globalSeo->meta_description ?? '' }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">

        @if(!empty($globalSeo?->og_image))
            <meta property="og:image" content="{{ asset('storage/'.$globalSeo->og_image) }}">
            <meta property="og:image:secure_url" content="{{ asset('storage/'.$globalSeo->og_image) }}">
        @endif

        <!-- ================= TWITTER CARD ================= -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $globalSeo->twitter_title ?? $globalSeo->default_title ?? config('app.name') }}">
        <meta name="twitter:description" content="{{ $globalSeo->twitter_description ?? $globalSeo->meta_description ?? '' }}">

        @if(!empty($globalSeo?->twitter_image))
            <meta name="twitter:image" content="{{ asset('storage/'.$globalSeo->twitter_image) }}">
        @elseif(!empty($globalSeo?->og_image))
            <meta name="twitter:image" content="{{ asset('storage/'.$globalSeo->og_image) }}">
        @endif

        <meta name="twitter:site" content="{{ $globalSeo->twitter_site ?? '' }}">
    @endif

    <!-- ================= FAVICON ================= -->
    @include('snippets.site.favicon')

    <!-- ================= GOOGLE VERIFICATION ================= -->
    @if(!empty($globalSeo?->google_verification))
        <meta name="google-site-verification" content="{{ $globalSeo->google_verification }}">
    @endif

    <!-- ================= GOOGLE ANALYTICS ================= -->
    @if(!empty($globalSeo?->google_analytics_id))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $globalSeo->google_analytics_id }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $globalSeo->google_analytics_id }}');
        </script>
    @endif

    <!-- ================= ORGANIZATION SCHEMA ================= -->
    @if(!empty($globalSeo?->site_logo))
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ $globalSeo->site_name ?? config('app.name') }}",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('storage/'.$globalSeo->site_logo) }}"
    }
    </script>
    @endif

    <!-- ================= STYLES ================= -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ route('theme.asset', ['css', 'bm-site.css']) }}?v={{ filemtime(public_path('assets/css/bm-site.css')) }}">
    @stack('styles')

    <!-- Alpine (like buttons) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</head>

<body class="bm">

    <!-- ================= PAGE LOADER ================= -->
    <div class="bm-loader" aria-hidden="true">
        <span class="bm-logo">
            <span class="bm-logo__mark">B</span>
            <span class="bm-logo__text">Brief<b>Media</b></span>
        </span>
    </div>

    <!-- ================= HEADER ================= -->
    @include('snippets.header')

    <!-- ================= CONTENT ================= -->
    <div class="bm-main">
        @yield('content')
    </div>

    <!-- ================= FOOTER ================= -->
    @include('snippets.footer')

    <!-- ================= SCRIPTS ================= -->
    <script src="{{ route('theme.asset', ['js', 'bm-site.js']) }}?v={{ filemtime(public_path('assets/js/bm-site.js')) }}"></script>

    <!-- Session messages (auth pages show them inline instead) -->
    @unless (View::hasSection('auth_page'))
        @foreach (['success', 'message', 'info', 'warning', 'error'] as $flashKey)
            @if (session($flashKey))
                <script>window.bmToast(@json(session($flashKey)), 4500);</script>
                @break
            @endif
        @endforeach
    @endunless

    @stack('scripts')
</body>
</html>
