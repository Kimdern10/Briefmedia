<!doctype html>
<html lang="en" class="theme-fs-sm" data-bs-theme-color="default" dir="ltr">


<!-- Mirrored from templates.iqonic.design/booksto-dist/html/admin/dashboard.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 26 Mar 2025 10:27:50 GMT -->
<head>
      <base href="/public">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>BerifMedia Management </title>
    <meta name="description"
        content="Omnisana ">
    <meta name="keywords"
        content="Omnisana Management">
    <meta name="author" content="Iqonic Design">
    <meta name="DC.title" content="Omnisana Management System">

    <script>
        (function () {
            const savedTheme = sessionStorage.getItem('Omnisana');

            if (savedTheme) {
                const settings = JSON.parse(savedTheme);
                const themeScheme = settings.setting.theme_scheme.value;
                document.documentElement.setAttribute('data-bs-theme', themeScheme);
            }
        })();
    </script>
</head>


    <meta name="setting_options" content='{&quot;saveLocal&quot;:&quot;sessionStorage&quot;,&quot;storeKey&quot;:&quot;booksto&quot;,&quot;setting&quot;:{&quot;app_name&quot;:{&quot;value&quot;:&quot;Booksto&quot;},&quot;theme_scheme_direction&quot;:{&quot;value&quot;:&quot;ltr&quot;},&quot;theme_scheme&quot;:{&quot;value&quot;:&quot;light&quot;},&quot;theme_color&quot;:{&quot;colors&quot;:{},&quot;value&quot;:&quot;default&quot;},&quot;theme_font_size&quot;:{&quot;value&quot;:&quot;theme-fs-md&quot;},&quot;page_layout&quot;:{&quot;value&quot;:&quot;container-fluid&quot;},&quot;sidebar_color&quot;:{&quot;value&quot;:&quot;sidebar-white&quot;},&quot;sidebar_type&quot;:{&quot;value&quot;:[]},&quot;sidebar_menu_style&quot;:{&quot;value&quot;:&quot;text-hover&quot;},&quot;theme_style_appearance&quot;:{&quot;value&quot;:{&quot;0&quot;:&quot;theme-default&quot;}},&quot;theme_transition&quot;:{&quot;value&quot;:&quot;theme-with-animation&quot;},&quot;header_navbar&quot;:{&quot;value&quot;:&quot;default&quot;},&quot;header_banner&quot;:{&quot;value&quot;:&quot;default&quot;},&quot;card_color&quot;:{&quot;value&quot;:&quot;card-default&quot;},&quot;footer&quot;:{&quot;value&quot;:&quot;default&quot;},&quot;body_font_family&quot;:{&quot;value&quot;:null},&quot;heading_font_family&quot;:{&quot;value&quot;:null}}}'>
    <!-- Google Font Api KEY-->
    <meta name="google_font_api" content="AIzaSyBG58yNdAjc20_8jAvLNSVi9E4Xhwjau_k">
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('admin_asset/images/Omnisana Hospital Logo Design.png') }}" />
    
    <!-- Library / Plugin Css Build -->
    <link rel="stylesheet" href="{{ asset('admin_asset/css/core/libs.min.css') }}" />
    
    <!-- flaticon css -->
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/flaticon/css/flaticon.css') }}" />
    
    <!-- font-awesome css -->
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/font-awesome/css/font-awesome.min.css') }}" />
    
    
    
    <!-- SwiperSlider css -->
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/swiperSlider/swiper.min.css') }}">
    
    
    <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    
    <!-- Flatpickr css -->
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/flatpickr/dist/flatpickr.min.css') }}" />
    
    
    
    <!-- booksto Design System Css -->
    <link rel="stylesheet" href="{{ asset('admin_asset/css/booksto.min5438.css?v=1.2.0') }}" />
    
    <!-- Custom Css -->
    <link rel="stylesheet" href="{{ asset('admin_asset/css/custom.min5438.css?v=1.2.0') }}" />
    
    <!-- RTL Css -->
    <link rel="stylesheet" href="{{ asset('admin_asset/css/rtl.min5438.css?v=1.2.0') }}" />
    
    <!-- Customizer Css -->
    <link rel="stylesheet" href="{{ asset('admin_asset/css/customizer.min5438.css?v=1.2.0') }}" />
    
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&amp;display=swap"
        rel="stylesheet">
    
    
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/remixicon/fonts/remixicon.css') }}" />
    
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/dripicons/webfont/webfont.css') }}" />
    
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/ionicons/css/ionicons.min.css') }}" />
    
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/line-awesome/css/line-awesome.min.css') }}" />
    
    <!-- Phosphor icons  -->
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/phosphor-icons/Fonts/regular/style.css') }}"></link>
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/phosphor-icons/Fonts/duotone/style.css') }}"></link>
    <link rel="stylesheet" href="{{ asset('admin_asset/vendor/phosphor-icons/Fonts/fill/style.css') }}"></link>
    
</head>

<body class="  ">
    <!-- loader Start -->
    <!-- <div id="loading">
        <div class="loader simple-loader">
            <div class="loader-body">
                <img src="/admin_asset/images/loader.gif" alt="loader" class="light-loader img-fluid " width="200">
            </div>
        </div>   
     </div> -->
    <!-- loader END -->

 
    <!-- aside -->
	@include('snippets.admin_sidebar')
	<!-- aside End -->
    <main class="main-content">
    <div class="position-sticky top-0 z-3">
      
        <!--Nav Start-->
        @include('snippets.admin_header')
        <!--Nav End-->

        @yield('content')
        
	    <!-- Footer -->
	    @include('snippets.admin_footer')
	    <!-- Footer End -->
    </div>
    </main>
	
	
  


        
      
    <!-- Library Bundle Script -->
    <script src="{{ asset('admin_asset/js/core/libs.min.js') }}"></script>
    <!-- Plugin Scripts -->
    <!-- Flatpickr Script -->
    <script src="{{ asset('admin_asset/vendor/flatpickr/dist/flatpickr.min.js') }}"></script>
    
    
    
    <!-- Slider-tab Script -->
    <script src="{{ asset('admin_asset/js/plugins/slider-tabs.js') }}"></script>
    
    
    
    
    <!-- Select2 Script -->
    <script src="{{ asset('admin_asset/js/plugins/select2.js') }}" defer></script>
    
    
    
    <!-- SwiperSlider Script -->
    <script src="{{ asset('admin_asset/vendor/swiperSlider/swiper.min.js') }}"></script>
    
    
    <!-- Lodash Utility -->
    <script src="{{ asset('admin_asset/vendor/lodash/lodash.min.js') }}"></script>
    <!-- Utilities Functions -->
    <script src="{{ asset('admin_asset/js/iqonic-script/utility.min.js') }}"></script>
    <!-- Settings Script -->
    <script src="{{ asset('admin_asset/js/iqonic-script/setting.min.js') }}"></script>
    <!-- Settings Init Script -->
    <script src="{{ asset('admin_asset/js/setting-init.js') }}"></script>
    <!-- External Library Bundle Script -->
    <script src="{{ asset('admin_asset/js/core/external.min.js') }}"></script>
    <!-- Dashboard Script -->
    <script src="{{ asset('admin_asset/js/Charts/dashboard.js') }}" defer></script>
    
    
    <!-- All Plugins Script -->
    
    
    
    
    <!-- Flatpickr Script -->
    <script src="{{ asset('admin_asset/vendor/flatpickr/dist/flatpickr.min.js') }}"></script>
    <script src="{{ asset('admin_asset/js/plugins/flatpickr.js') }}" defer></script>
    
    
    <!-- Select2 Script -->
    <script src="{{ asset('admin_asset/js/plugins/select2.js') }}" defer></script>
    
    
    
    <!-- All charts Script -->
    
    
    
    
    
    <script src="{{ asset('admin_asset/js/vertical_slider.js') }}" defer></script>
    
    
    <!-- Hopeui Script -->
    <script src="{{ asset('admin_asset/js/booksto5438.js?v=1.2.0') }}" defer></script>
    <script src="{{ asset('admin_asset/js/booksto-advance5438.js?v=1.2.0') }}" defer></script>
    
    <script src="{{ asset('admin_asset/js/sidebar5438.js?v=1.2.0') }}" defer></script>
    
    
    <script src="{{ asset('admin_asset/js/plugins/select2.js') }}" defer></script>
    <script src="{{ asset('admin_asset/js/Setting/enchanter.js') }}" defer></script>
    
    
    <!--morris chart -->
    <script src="../../../../cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js') }}"></script>
    <script src="../../../../cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js') }}"></script>
    
    
    
    <!--highcharts chart -->
    <script src="../../../../code.highcharts.com/highcharts.html"></script>
    <script src="../../../../code.highcharts.com/highcharts-more.html"></script>
    <script src="../../../../code.highcharts.com/modules/exporting.html"></script>
    
    
    
    <!--Am chart -->
    <script src="../../../../cdn.amcharts.com/lib/4/core.js"></script>
    <script src="../../../../cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="../../../../cdn.amcharts.com/lib/4/themes/animated.js"></script>
    
    <!--Widget Chart -->
    <!--Custom js -->

       <!-- ✅ SweetAlert (Offline replacement for CDN) -->
    <script src="{{ asset('admin_asset/vendor/sweetalert/sweetalert.min.js') }}"></script>

    @if (session('message'))
    <script>
        swal("Successful!", "{{ session('message') }}", "success");
    </script>
    @endif

    @if (session('info'))
    <script>
        swal("Info", "{{ session('info') }}", "info");
    </script>
    @endif

    @if (Session::has('success'))
    <script>
        swal("Successful!", "{{ Session::get('success') }}", "success");
    </script>
    @endif

    @if (Session::has('error'))
    <script>
        swal("Error!", "{{ Session::get('error') }}", "warning");
    </script>
    @endif

    @if (Session::has('warning'))
<script>
    swal("Warning!", "{{ Session::get('warning') }}", "warning");
</script>
@endif

</body>

<!-- Mirrored from templates.iqonic.design/booksto-dist/html/admin/dashboard.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 26 Mar 2025 10:27:50 GMT -->
</html>