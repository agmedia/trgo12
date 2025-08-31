<!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand text-primary">
                <!-- ========   Change your logo from here   ============ -->
                <img src="{{ asset('admin/assets/images/logo-dark.svg') }}" class="img-fluid logo-lg" alt="logo" />
            </a>
        </div>
        <div class="navbar-content">
            <div class="card pc-user-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <img src="{{ asset('admin/assets/images/user/avatar-1.jpg') }}" alt="user-image" class="user-avtar wid-45 rounded-circle" />
                        </div>
                        <div class="flex-grow-1 ms-3 me-2">
                            <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                            <small>Administrator</small>
                        </div>
                        <a class="btn btn-icon btn-link-secondary avtar" data-bs-toggle="collapse" href="#pc_sidebar_userlink">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-sort-outline"></use>
                            </svg>
                        </a>
                    </div>
                    <div class="collapse pc-user-links" id="pc_sidebar_userlink">
                        <div class="pt-3">
                            <a href="{{ route('settings.profile') }}">
                                <i class="ti ti-user"></i>
                                <span>My Account</span>
                            </a>
                            <a href="{{ route('settings') }}">
                                <i class="ti ti-settings"></i>
                                <span>Settings</span>
                            </a>
                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="ti ti-power"></i>
                                <span>Logout</span>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="pc-navbar">
                <li class="pc-item">
                    <a href="{{ route('dashboard') }}" class="pc-link">
                            <span class="pc-micon">
                                <svg class="pc-icon">
                                    <use xlink:href="#custom-element-plus"></use>
                                </svg>
                            </span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>
                <!-- Catalog -->
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link">
                        <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-layer"></use></svg></span>
                        <span class="pc-mtext" data-i18n="Dashboard">Katalog</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="../dashboard/index.html" data-i18n="Default">Kategorije</a></li>
                        <li class="pc-item"><a class="pc-link" href="../dashboard/analytics.html" data-i18n="Analytics">Artikli</a></li>
                        <li class="pc-item"><a class="pc-link" href="../dashboard/finance.html" data-i18n="Finance">Izdavači</a></li>
                        <li class="pc-item"><a class="pc-link" href="../dashboard/finance.html" data-i18n="Finance">Autori</a></li>
                    </ul>
                </li>

                <li class="pc-item">
                    <a href="#{{--{{ route('apartments.index') }}--}}" class="pc-link">
                        <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-fatrows"></use></svg></span>
                        <span class="pc-mtext">Narudžbe</span>
                    </a>
                </li>

                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link">
                        <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-status-up"></use></svg></span>
                        <span class="pc-mtext" data-i18n="Dashboard">Marketing</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="../dashboard/index.html" data-i18n="Default">Akcije</a></li>
                        <li class="pc-item"><a class="pc-link" href="../dashboard/analytics.html" data-i18n="Analytics">Blog</a></li>
                        <li class="pc-item"><a class="pc-link" href="../dashboard/analytics.html" data-i18n="Analytics">Widgets</a></li>
                    </ul>
                </li>

                <li class="pc-item">
                    <a href="#{{--{{ route('apartments.index') }}--}}" class="pc-link">
                        <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-profile-2user-outline"></use></svg></span>
                        <span class="pc-mtext">Korisnici</span>
                    </a>
                </li>

                <!-- Settings -->
                <li class="pc-item pc-caption">
                    <label>Postavke</label>
                    <svg class="pc-icon">
                        <use xlink:href="#custom-setting-2"></use>
                    </svg>
                </li>

                <li class="pc-item">
                    <a href="{{ route('settings') }}" class="pc-link">
                            <span class="pc-micon">
                                <svg class="pc-icon">
                                    <use xlink:href="#custom-user"></use>
                                </svg>
                            </span>
                        <span class="pc-mtext">Moj profil</span>
                    </a>
                </li>

                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link">
                        <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-setting-2"></use></svg></span>
                        <span class="pc-mtext" data-i18n="Dashboard">Postavke</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="../dashboard/index.html" data-i18n="Default">Akcije</a></li>
                        <li class="pc-item"><a class="pc-link" href="../dashboard/analytics.html" data-i18n="Analytics">Blog</a></li>
                        <li class="pc-item"><a class="pc-link" href="../dashboard/analytics.html" data-i18n="Analytics">Widgets</a></li>
                    </ul>
                </li>

                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link">
                        <span class="pc-micon"><svg class="pc-icon"><use xlink:href="#custom-presentation-chart"></use></svg></span>
                        <span class="pc-mtext" data-i18n="Dashboard">Trgovina</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="../dashboard/index.html" data-i18n="Default">Akcije</a></li>
                        <li class="pc-item"><a class="pc-link" href="../dashboard/analytics.html" data-i18n="Analytics">Blog</a></li>
                        <li class="pc-item"><a class="pc-link" href="../dashboard/analytics.html" data-i18n="Analytics">Widgets</a></li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</nav>
<!-- [ Sidebar Menu ] end -->