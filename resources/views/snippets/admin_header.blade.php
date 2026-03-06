<nav class="nav navbar navbar-expand-xl navbar-light iq-navbar border-bottom">
    <div class="container-fluid navbar-inner">
        <!-- Sidebar toggle -->
        <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
            <i class="icon d-flex">
                <svg class="icon-20" width="20" viewBox="0 0 24 24">
                    <path fill="currentColor"
                        d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" />
                </svg>
            </i>
        </div>

        <!-- Spacer to push logout to right -->
        <div class="d-flex align-items-center ms-auto">
            <a class="btn btn-primary d-flex align-items-center gap-1"
               href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Sign out <i class="ph ph-sign-out"></i>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>
</nav>

@push('styles')
<style>
    /* Mobile navbar adjustments */
    @media (max-width: 767px) {
        .iq-navbar {
            padding: 8px 10px !important;
        }

        .iq-navbar .sidebar-toggle i svg {
            width: 18px !important;
            height: 18px !important;
        }

        .iq-navbar .btn {
            font-size: 14px !important;
            padding: 6px 10px !important;
        }

        .iq-navbar .btn i {
            font-size: 14px !important;
        }

        .iq-navbar .d-flex.align-items-center.ms-auto {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
    }
</style>
@endpush
