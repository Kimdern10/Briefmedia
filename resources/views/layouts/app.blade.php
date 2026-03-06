<!doctype html>
<html lang="en">
<head>
 <base href="/public">
    <!-- ================= META ================= -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- ================= TITLE ================= -->
    <title>{{ $globalSeo->default_title ?? config('app.name') }}</title>

    <!-- ================= BASIC SEO ================= -->
    <meta name="description" content="{{ $globalSeo->meta_description ?? '' }}">
    <meta name="keywords" content="{{ $globalSeo->meta_keywords ?? '' }}">
    <meta name="author" content="{{ $globalSeo->author ?? '' }}">
    <meta name="robots" content="{{ $globalSeo->robots_meta ?? 'index, follow' }}">
    <link rel="canonical" href="{{ $globalSeo->canonical_url ?? url()->current() }}">

    <!-- ================= OPEN GRAPH ================= -->
    <meta property="og:title" content="{{ $globalSeo->og_title ?? $globalSeo->default_title ?? config('app.name') }}">
    <meta property="og:description" content="{{ $globalSeo->og_description ?? $globalSeo->meta_description ?? '' }}">
    <meta property="og:type" content="{{ $globalSeo->og_type ?? 'website' }}">
    <meta property="og:url" content="{{ url()->current() }}">

    @if(!empty($globalSeo?->og_image))
        <meta property="og:image" content="{{ asset('storage/'.$globalSeo->og_image) }}">
    @endif

    <!-- ================= TWITTER CARD ================= -->
    <meta name="twitter:card" content="{{ $globalSeo->twitter_card ?? 'summary' }}">
    <meta name="twitter:title" content="{{ $globalSeo->twitter_title ?? $globalSeo->default_title ?? config('app.name') }}">
    <meta name="twitter:description" content="{{ $globalSeo->twitter_description ?? $globalSeo->meta_description ?? '' }}">

    @if(!empty($globalSeo?->twitter_image))
        <meta name="twitter:image" content="{{ asset('storage/'.$globalSeo->twitter_image) }}">
    @endif

    <meta name="twitter:site" content="{{ $globalSeo->twitter_site ?? '' }}">

    <!-- ================= FAVICON ================= -->
    @if(!empty($globalSeo?->favicon))
        <link rel="icon" type="image/png" href="{{ asset('storage/'.$globalSeo->favicon) }}">
    @endif

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
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/share-buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    <!-- Alpine -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
      <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ================= PAGE LOADER STYLE ================= -->
    <style>
        body > .page-loader {
            position: fixed;
            inset: 0;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        body > .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .page-loader-logo {
            width: 240px;
            max-width: 85vw;
            height: auto;
            animation: pageLoaderPulse 1.5s ease-in-out infinite;
        }

        @keyframes pageLoaderPulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.08); opacity: 0.75; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>

</head>

<body>

    <!-- ================= PAGE LOADER ================= -->
    <div class="page-loader">
        <img src="{{ asset('assets/img/logo/ChatGPT.png') }}" 
             alt="Loading" 
             class="page-loader-logo">
    </div>

    <!-- ================= HEADER ================= -->
    @include('snippets.header')

    <!-- ================= CONTENT ================= -->
    @yield('content')

    <!-- ================= FOOTER ================= -->
    @include('snippets.footer')

    <!-- ================= SCRIPTS ================= -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/masonry.min.js') }}"></script>
    <script src="{{ asset('assets/js/theia-sticky-sidebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/ajax-contact.js') }}"></script>
    <script src="{{ asset('assets/js/switch.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Loader Hide -->
    <script>
        window.addEventListener("load", function () {
            const loader = document.querySelector(".page-loader");
            loader.classList.add("hidden");
            setTimeout(() => loader.remove(), 600);
        });
    </script>

   
<!-- SweetAlert session messages -->
@if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: "{{ session('success') }}",
        });
    </script>
@endif

@if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: "{{ session('error') }}",
        });
    </script>
@endif

@if (session('warning'))
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Warning!',
            text: "{{ session('warning') }}",
        });
    </script>
@endif

@if (session('info'))
    <script>
        Swal.fire({
            icon: 'info',
            title: 'Info',
            text: "{{ session('info') }}",
        });
    </script>
@endif

    <!-- Share Buttons -->
    <script src="{{ asset('js/share-buttons.js') }}"></script>

</body>
</html>