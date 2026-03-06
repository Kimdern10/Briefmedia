<header class="header fixed-top navbar-expand-xl">
    <div class="container-fluid">
        <div class="header__main">

            <!-- logo -->
            <div class="logo">
                <a class="logo__link logo--dark" href="/">
                    <img src="{{ asset('assets/img/logo/ChatGPT.png') }}" alt="" class="logo__img">
                </a>
                <a class="logo__link logo--light" href="/">
                    <img src="{{ asset('assets/img/logo/ChatGPT_Image.png') }}" alt="" class="logo__img">
                </a>
            </div>
            
            <!-- Hamburger (small screens only) -->
            <button class="navbar-toggler d-xl-none" type="button" 
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent"
                    aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="header__navbar">
                <nav class="navbar">
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav">

                            <!-- Home -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Home</a>
                            </li>

                            <!-- Dynamic Categories - First 5 -->
                            @php
                                $allCategories = \App\Models\Category::all();
                                $navbarCategories = $allCategories->take(5);
                                $pageCategories = $allCategories->skip(5);
                            @endphp

                            @foreach($navbarCategories as $category)
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('category.page', $category->slug) }}">
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endforeach

                            <!-- Pages Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="javascript:void(0)"
                                   data-bs-toggle="dropdown">Pages</a>
                                <ul class="dropdown-menu">
                                    <!-- Remaining Categories -->
                                    @if($pageCategories->isNotEmpty())
                                        @foreach($pageCategories as $category)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('category.page', $category->slug) }}">
                                                    {{ $category->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                        <li><hr class="dropdown-divider"></li>
                                    @endif

                                    <!-- Static Pages -->
                                    <li><a class="dropdown-item" href="{{ route('contact') }}">Contact us</a></li>
                                    
                                    @auth
                                        <li><hr class="dropdown-divider"></li>
                                        <!-- Dashboard for Admin -->
                                        @if(Auth::user()->role_as == 1)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                                    <i class="bi bi-speedometer2"></i> Dashboard
                                                </a>
                                            </li>
                                        @endif
                                        
                                        <!-- Logout -->
                                        <li>
                                            <a class="dropdown-item" href="{{ route('logout') }}"
                                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                <i class="bi bi-box-arrow-right"></i> Logout
                                            </a>
                                        </li>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    @endauth

                                    @guest
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                                        <li><a class="dropdown-item" href="{{ route('sign-up') }}"><i class="bi bi-person-plus"></i> Sign up</a></li>
                                    @endguest
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>

            <!-- header actions -->
            <div class="header__action-items d-flex align-items-center">

                <!-- User Profile Image -->
              <div class="ms-3">
    @auth
        @php
            $profileImage = optional(Auth::user()->Userprofile)->profile_picture
                ? asset('Userprofile/' . Auth::user()->Userprofile->profile_picture)
                : asset('assets/img/user/1.jpg');
        @endphp
        <a href="{{ route('profile') }}" class="profile-link">
            <img src="{{ $profileImage }}" 
                 alt="Profile" 
                 class="profile-image"
                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=ff69b4&color=fff&size=150'; this.style.backgroundColor='transparent';">
        </a>
    @endauth
</div>

                <!-- Theme switch -->
                <div class="theme-switch ms-3">
                    <label class="theme-switch__label" for="checkbox">
                        <input type="checkbox" id="checkbox" class="theme-switch__checkbox">
                        <span class="theme-switch__slider round">
                            <i class="bi bi-sun icon-light theme-switch__icon theme-switch__icon--light"></i>
                            <i class="bi bi-moon icon-dark theme-switch__icon theme-switch__icon--dark"></i>
                        </span>
                    </label>
                </div>

                <!-- Search icon -->
               <div class="search-wrapper  position-relative d-flex align-items-center">
    <!-- Search Icon (exactly as original, untouched) -->
    <div class="search-icon ms-3">
        <a href="{{ route('user.posts.search') }}" class="search-icon__link" id="searchToggle">
            <i class="bi bi-search search-icon__icon"></i>
        </a>
    </div>
    
    <!-- Search Input (hidden by default) -->
    <div class="search-input-container overflow-hidden" style="width: 0; transition: width 0.3s ease;">
        <form action="{{ route('user.posts.search') }}" method="GET" class="d-flex">
            <input type="text" 
                   name="query" 
                   class="form-control form-control-sm" 
                   placeholder="Search posts..." 
                   aria-label="Search"
                   id="searchInput"
                   required>
        </form>
    </div>
</div>

<!-- No additional styles that could affect the icon -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchToggle = document.getElementById('searchToggle');
    const searchContainer = document.querySelector('.search-input-container');
    const searchInput = document.getElementById('searchInput');
    let isOpen = false;

    searchToggle.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (!isOpen) {
            // Slide open
            searchContainer.style.width = '200px';
            setTimeout(() => {
                searchInput.focus();
            }, 300);
            isOpen = true;
        } else {
            // Slide closed
            searchContainer.style.width = '0';
            isOpen = false;
        }
    });

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-wrapper')) {
            if (isOpen) {
                searchContainer.style.width = '0';
                isOpen = false;
            }
        }
    });

    // Submit form on Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.closest('form').submit();
        }
    });
});
</script>

            </div>
            
            <!--navbar-toggler-->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarSupportedContent" 
                    aria-controls="navbarSupportedContent" 
                    aria-expanded="false" 
                    aria-label="Toggle navigation">
                <span class="navbar-toggler__icon"></span>
            </button>
        </div>
    </div>
</header>

<!-- Profile Image Styles -->
<style>
.profile-link {
    display: inline-block;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    overflow: hidden;
    transition: 0.3s;
    margin-top: 6px;
}

.profile-link:hover {
    opacity: 0.8;
}

.profile-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border: 2px solid #fff;
}

/* Dropdown icon spacing */
.dropdown-item i {
    margin-right: 8px;
    font-size: 1rem;
}

/* Active state for home */
.nav-link.active {
    color: #007bff;
}


</style>