<!doctype html>
<html lang="en" class="theme-fs-sm" data-bs-theme-color="default" dir="ltr">

<head>
    <base href="/public">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>BriefMedia Management</title>

    <meta name="description" content="BriefMedia">
    <meta name="keywords" content="BriefMedia Management">
    <meta name="author" content="BriefMedia">
    <meta name="DC.title" content="BriefMedia Management System">

    <!-- Theme script -->
    <script>
        (function () {
            const savedTheme = sessionStorage.getItem('BriefMedia_theme_settings');

            if (savedTheme) {
                const settings = JSON.parse(savedTheme);
                const themeScheme = settings.setting.theme_scheme.value;
                document.documentElement.setAttribute('data-bs-theme', themeScheme);
            }
        })();
    </script>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('admin_asset/images/ChatGPT images Feb.png') }}" />

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('admin_asset/css/core/libs.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/flaticon/css/flaticon.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/font-awesome/css/font-awesome.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/swiperSlider/swiper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/flatpickr/dist/flatpickr.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('admin_asset/css/booksto.min5438.css?v=1.2.0') }}" />
    <link rel="stylesheet" href="{{ asset('admin_asset/css/custom.min5438.css?v=1.2.0') }}" />
    <link rel="stylesheet" href="{{ asset('admin_asset/css/rtl.min5438.css?v=1.2.0') }}" />
    <link rel="stylesheet" href="{{ asset('admin_asset/css/customizer.min5438.css?v=1.2.0') }}" />

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/remixicon/fonts/remixicon.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/line-awesome/css/line-awesome.min.css') }}" />

    <!-- Phosphor -->
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/phosphor-icons/Fonts/regular/style.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/phosphor-icons/Fonts/duotone/style.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/phosphor-icons/Fonts/fill/style.css') }}">

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Page styles --}}
    @stack('styles')
</head>

<body>

    <!-- loader (optional) -->
    <!-- <div id="loading">...</div> -->

    <!-- ================= SIDEBAR ================= -->
    @include('snippets.admin_sidebar')

    <!-- ================= MAIN CONTENT ================= -->
    <main class="main-content">

        <div class="position-sticky top-0 z-3">

            <!-- ================= HEADER ================= -->
            @include('snippets.admin_header')

            <!-- ================= PAGE CONTENT ================= -->
            @yield('content')

            <!-- ================= FOOTER ================= -->
            @include('snippets.admin_footer')

        </div>
    </main>

    <!-- ================= SCRIPTS ================= -->

    <script src="{{ asset('admin_asset/js/core/libs.min.js') }}"></script>
    <script src="{{ asset('admin_asset/vendor/flatpickr/dist/flatpickr.min.js') }}"></script>
    <script src="{{ asset('admin_asset/vendor/swiperSlider/swiper.min.js') }}"></script>
    <script src="{{ asset('admin_asset/vendor/lodash/lodash.min.js') }}"></script>

    <script src="{{ asset('admin_asset/js/iqonic-script/utility.min.js') }}"></script>
    <script src="{{ asset('admin_asset/js/iqonic-script/setting.min.js') }}"></script>
    <script src="{{ asset('admin_asset/js/setting-init.js') }}"></script>
    <script src="{{ asset('admin_asset/js/core/external.min.js') }}"></script>

    <script src="{{ asset('admin_asset/js/booksto5438.js?v=1.2.0') }}"></script>
    <script src="{{ asset('admin_asset/js/booksto-advance5438.js?v=1.2.0') }}"></script>
    <script src="{{ asset('admin_asset/js/sidebar5438.js?v=1.2.0') }}"></script>

    <!-- SweetAlert fallback -->
    <script src="{{ asset('admin_asset/vendor/sweetalert/sweetalert.min.js') }}"></script>

    @if (session('message'))
        <script>swal("Success!", "{{ session('message') }}", "success");</script>
    @endif

    @if (session('error'))
        <script>swal("Error!", "{{ session('error') }}", "error");</script>
    @endif

    @if (session('warning'))
        <script>swal("Warning!", "{{ session('warning') }}", "warning");</script>
    @endif

    {{-- Page scripts --}}
    @stack('scripts')

</body>
</html>