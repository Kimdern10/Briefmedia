<aside class="sidebar sidebar-base" id="first-tour" data-toggle="main-sidebar" data-sidebar="responsive" style="height: 100vh; overflow-y: auto;">
    <div class="sidebar-header d-flex align-items-center justify-content-start position-relative">
        <div class="logo pull-left">
            <a href="/" class="img-responsive">
                <img src="{{ asset('assets/img/logo/ChatGPT.png') }}"
                     alt="BriefMedia Logo"
                     style="width: 180px; height: auto;"> <!-- bigger logo -->
            </a>
        </div>

        <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
            <i class="icon">
                <svg width="20" viewBox="0 0 24 24" fill="none">
                    <path d="M15.5 19L8.5 12L15.5 5"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"></path>
                </svg>
            </i>
        </div>
    </div>

    <div class="sidebar-body pt-0 pb-2">
        <div class="sidebar-list">
            <ul class="navbar-nav iq-main-menu">

                <!-- MAIN -->
                <li class="nav-item static-item mb-1">
                    <a class="nav-link static-item disabled">
                        <span class="default-icon">Main</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link" href="/">
                        <i class="ph-duotone ph-house"></i>
                        <span class="item-name">Visit Website</span>
                    </a>
                </li>

                <!-- ADMIN -->
                <li class="nav-item static-item mt-1 mb-1">
                    <a class="nav-link static-item disabled">
                        <span class="default-icon">Blog Admin</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                        <i class="ph-duotone ph-gauge"></i>
                        <span class="item-name">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link" href="{{ route('user.list') }}">
                        <i class="ph-duotone ph-users"></i>
                        <span class="item-name">Users</span>
                    </a>
                </li>

                <!-- CATEGORIES -->
                <li class="nav-item static-item mt-1 mb-1">
                    <a class="nav-link static-item disabled">
                        <span class="default-icon">Categories</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link" href="{{ route('admin.categories.index') }}">
                        <i class="ph-duotone ph-folders"></i>
                        <span class="item-name">All Categories</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link" href="{{ route('admin.categories.create') }}">
                        <i class="ph-duotone ph-plus-circle"></i>
                        <span class="item-name">Add Category</span>
                    </a>
                </li>

                <!-- POSTS -->
                <li class="nav-item static-item mt-1 mb-1">
                    <a class="nav-link static-item disabled">
                        <span class="default-icon">Posts</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link" href="{{ route('admin.posts.index') }}">
                        <i class="ph-duotone ph-newspaper"></i>
                        <span class="item-name">All Posts</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link" href="{{ route('admin.posts.create') }}">
                        <i class="ph-duotone ph-note-pencil"></i>
                        <span class="item-name">Create Post</span>
                    </a>
                </li>

                <!-- NEWSLETTER -->
                <li class="nav-item static-item mt-1 mb-1">
                    <a class="nav-link static-item disabled">
                        <span class="default-icon">Newsletter</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link" href="{{ route('admin.subscribers') }}">
                        <i class="ph-duotone ph-envelope-simple"></i>
                        <span class="item-name">Subscribers</span>
                    </a>
                </li>

                <!-- COMMENTS -->
                <li class="nav-item static-item mt-1 mb-1">
                    <a class="nav-link static-item disabled">
                        <span class="default-icon">Comments</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link" href="{{ route('admin.comments') }}">
                        <i class="ph-duotone ph-chat-text"></i>
                        <span class="item-name">Comments</span>
                    </a>
                </li>

                <!-- SETTINGS -->
                <li class="nav-item static-item mt-1 mb-1">
                    <a class="nav-link static-item disabled">
                        <span class="default-icon">Settings</span>
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link" href="{{ route('admin.seo.edit') }}">
                        <i class="ph-duotone ph-gear"></i>
                        <span class="item-name">SEO Settings</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</aside>