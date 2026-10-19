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
        @php
            // Each page's own title/description (sections 'title' and 'meta_description'), falling
            // back to Admin → SEO settings. Section content is already escaped by Blade.
            $seoIsHome = request()->is('/');
            $seoSiteName = $globalSeo->site_name ?? config('app.name');
            $seoTitle = View::hasSection('title') ? trim(View::yieldContent('title')) : e($globalSeo->default_title ?? $seoSiteName);
            $seoDescription = trim(View::yieldContent('meta_description')) ?: e($globalSeo->meta_description ?? '');
            $seoShareTitle = $seoIsHome && !empty($globalSeo?->og_title) ? e($globalSeo->og_title) : $seoTitle;
            $seoShareDescription = $seoIsHome && !empty($globalSeo?->og_description) ? e($globalSeo->og_description) : $seoDescription;

            // Every page is its own canonical address (page 2, 3… of a list keep their number)
            $seoPage = (int) request()->query('page', 1);
            $seoCanonical = $seoIsHome ? url('/') : url()->current().($seoPage > 1 ? '?page='.$seoPage : '');

            // Let Google show big photos and full snippets (Discover, Top Stories), unless the page is noindex
            $seoRobots = trim(View::yieldContent('robots')) ?: ($globalSeo->robots_meta ?? 'index, follow');
            if (! str_contains($seoRobots, 'noindex') && ! str_contains($seoRobots, 'max-image-preview')) {
                $seoRobots .= ', max-image-preview:large, max-snippet:-1';
            }

            $seoImage = !empty($globalSeo?->og_image) ? url('storage/'.$globalSeo->og_image) : null;
            $seoTwitterImage = !empty($globalSeo?->twitter_image) ? url('storage/'.$globalSeo->twitter_image) : $seoImage;
        @endphp

        <!-- ================= TITLE ================= -->
        <title>{!! $seoTitle !!}</title>

        <!-- ================= BASIC SEO ================= -->
        <meta name="description" content="{!! $seoDescription !!}">
        <meta name="author" content="{{ $globalSeo->author ?? $seoSiteName }}">
        <meta name="robots" content="{{ $seoRobots }}">
        <link rel="canonical" href="{{ $seoCanonical }}">

        <!-- ================= OPEN GRAPH ================= -->
        <meta property="og:site_name" content="{{ $seoSiteName }}">
        <meta property="og:locale" content="en_NG">
        <meta property="og:title" content="{!! $seoShareTitle !!}">
        <meta property="og:description" content="{!! $seoShareDescription !!}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $seoCanonical }}">
        @if($seoImage)
            <meta property="og:image" content="{{ $seoImage }}">
            <meta property="og:image:secure_url" content="{{ $seoImage }}">
        @endif

        <!-- ================= TWITTER CARD ================= -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{!! $seoIsHome && !empty($globalSeo?->twitter_title) ? e($globalSeo->twitter_title) : $seoShareTitle !!}">
        <meta name="twitter:description" content="{!! $seoIsHome && !empty($globalSeo?->twitter_description) ? e($globalSeo->twitter_description) : $seoShareDescription !!}">
        @if($seoTwitterImage)
            <meta name="twitter:image" content="{{ $seoTwitterImage }}">
        @endif
        @if(!empty($globalSeo?->twitter_site))
            <meta name="twitter:site" content="{{ $globalSeo->twitter_site }}">
        @endif
    @endif

    {{-- Things a page wants loaded early, e.g. its main photo (@push('head')) --}}
    @stack('head')

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

    <!-- ================= ORGANIZATION & SITE SEARCH SCHEMA ================= -->
    @php
        $siteSchema = [array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'NewsMediaOrganization',
            'name' => $globalSeo->site_name ?? config('app.name'),
            'url' => url('/'),
            'logo' => !empty($globalSeo?->site_logo) ? url('storage/'.$globalSeo->site_logo) : null,
        ])];
        if (request()->is('/')) {
            // Lets Google show a search box for the site in results
            $siteSchema[] = [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $globalSeo->site_name ?? config('app.name'),
                'url' => url('/'),
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => route('user.posts.search').'?query={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ];
        }
    @endphp
    @foreach($siteSchema as $schemaItem)
        <script type="application/ld+json">{!! json_encode($schemaItem, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endforeach

    <!-- ================= STYLES ================= -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Fonts and icons load in the background so they don't hold up the first paint on phones
         (text shows in the system font for a moment, then switches). The helper turns a loaded
         preload into a stylesheet once, so page navigation never adds the same file twice. --}}
    <script>
        window.bmUseStylesheet = function (link) {
            link.onload = null;
            var sheets = document.querySelectorAll('link[rel="stylesheet"]');
            for (var i = 0; i < sheets.length; i++) {
                if (sheets[i].href === link.href) { link.parentNode && link.parentNode.removeChild(link); return; }
            }
            link.rel = 'stylesheet';
        };
    </script>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800&display=swap" onload="bmUseStylesheet(this)">
    <link rel="preload" as="style" href="{{ asset('assets/css/font/bootstrap-icons.css') }}" onload="bmUseStylesheet(this)">
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('assets/css/font/fonts/bootstrap-icons0107.woff2') }}?dd67030699838ea613ee6dbda90effa6" crossorigin>
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800&display=swap">
        <link rel="stylesheet" href="{{ asset('assets/css/font/bootstrap-icons.css') }}">
    </noscript>
    <link rel="stylesheet" href="{{ route('theme.asset', ['css', 'bm-site.css']) }}?v={{ filemtime(public_path('assets/css/bm-site.css')) }}">
    @stack('styles')

    {{-- Livewire (its script also provides Alpine.js, used by the like buttons) --}}
    @livewireStyles
</head>

<body class="bm">

    {{-- No full-screen loader: it hid the page until every photo had downloaded, which made
         first visits feel slow on phones. The page now shows as soon as it arrives. --}}

    <!-- ================= HEADER ================= -->
    @include('snippets.header')

    <!-- ================= CONTENT ================= -->
    <div class="bm-main">
        @yield('content')
    </div>

    <!-- ================= FOOTER ================= -->
    @include('snippets.footer')

    <!-- ================= SCRIPTS ================= -->
    {{-- data-navigate-once: these run on the first visit only; pages after that swap in without a reload (bm-navigate.js) --}}
    <script src="{{ route('theme.asset', ['js', 'bm-navigate.js']) }}?v={{ filemtime(public_path('assets/js/bm-navigate.js')) }}" data-navigate-once></script>
    <script src="{{ route('theme.asset', ['js', 'bm-site.js']) }}?v={{ filemtime(public_path('assets/js/bm-site.js')) }}" data-navigate-once></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js" data-navigate-once></script>
    <script src="{{ route('theme.asset', ['js', 'bm-alerts.js']) }}?v={{ filemtime(public_path('assets/js/bm-alerts.js')) }}" data-navigate-once></script>

    <!-- Session messages (auth pages show them inline instead) -->
    @unless (View::hasSection('auth_page'))
        @foreach (['success' => 'success', 'message' => 'success', 'info' => 'info', 'warning' => 'warning', 'error' => 'error'] as $flashKey => $flashTone)
            @if (session($flashKey))
                <script>window.bmToast(@json(session($flashKey)), @json($flashTone), 4500);</script>
                @break
            @endif
        @endforeach
    @endunless

    @livewireScripts

    {{-- Breaking news push alerts (OneSignal). Only loads once ONESIGNAL_APP_ID is set in .env --}}
    @if (config('services.onesignal.app_id'))
        <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer data-navigate-once></script>
        <script data-navigate-once>
            window.OneSignalDeferred = window.OneSignalDeferred || [];
            OneSignalDeferred.push(async (OneSignal) => {
                await OneSignal.init({
                    appId: @json(config('services.onesignal.app_id')),
                    serviceWorkerPath: 'OneSignalSDKWorker.js',
                    notifyButton: { enable: false },
                });

                const updateButtons = () => document.querySelectorAll('[data-push-optin]').forEach((button) => {
                    const subscribed = OneSignal.User.PushSubscription.optedIn;
                    button.hidden = false;
                    button.disabled = subscribed;
                    button.querySelector('span').textContent = subscribed ? 'Breaking news alerts are on' : 'Get breaking news alerts';
                });
                updateButtons();
                OneSignal.User.PushSubscription.addEventListener('change', updateButtons);
                // Each page swaps in a new footer, so refresh its button too
                document.addEventListener('livewire:navigated', updateButtons);

                document.addEventListener('click', async (event) => {
                    if (!event.target.closest('[data-push-optin]')) { return; }
                    await OneSignal.Notifications.requestPermission();
                    await OneSignal.User.PushSubscription.optIn();
                    updateButtons();
                });
            });
        </script>
    @endif

    @stack('scripts')
</body>
</html>
