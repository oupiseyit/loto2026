<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin — HT ភ្នាក់' }}</title>

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- AdminLTE 3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <!-- Tailwind (for Livewire partials that rely on utility classes) -->
    @vite(['resources/css/app.css'])

    @livewireStyles
    @stack('styles')

    <style>
        /* Ensure AdminLTE sidebar and nav sit above Tailwind resets */
        .main-sidebar { z-index: 1038 !important; }
        .main-header  { z-index: 1039 !important; }
        /* Gold accent on active nav links */
        .nav-sidebar .nav-link.active,
        .nav-sidebar .nav-link.active:hover { background-color: #D4A017 !important; color: #fff !important; }
        .nav-sidebar .nav-link:hover { background-color: rgba(255,255,255,0.1) !important; }
        /* Sidebar icon/text colour */
        .nav-sidebar .nav-link { color: rgba(255,255,255,0.85) !important; }
        .nav-sidebar .nav-icon  { color: rgba(255,255,255,0.7) !important; }
        .nav-sidebar .nav-link.active .nav-icon { color: #fff !important; }
        /* Brand */
        .brand-link:hover { background-color: rgba(0,0,0,0.1) !important; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    {{-- ========== TOP NAVBAR ========== --}}
    <nav class="main-header navbar navbar-expand navbar-white navbar-light"
         style="border-bottom: 2px solid #D4A017;">

        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto align-items-center">
            <li class="nav-item mr-3 d-none d-sm-flex align-items-center">
                <i class="fas fa-user-shield mr-1" style="color:#D4A017;"></i>
                <span class="font-weight-bold" style="color:#DC143C; font-size:.85rem;">
                    {{ auth()->user()->name ?? auth()->user()->username }}
                </span>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-sm btn-danger px-3 py-1" style="background-color:#DC143C;border-color:#DC143C;color:#fff;"
                   href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
                <form id="admin-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </li>
        </ul>
    </nav>

    {{-- ========== SIDEBAR ========== --}}
    <aside class="main-sidebar elevation-4" style="background-color: #DC143C;">

        {{-- Brand --}}
        <a href="{{ route('home') }}" class="brand-link d-flex align-items-center"
           style="background-color:#B80010; border-bottom:1px solid rgba(255,255,255,0.15);">
            <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:33px;height:33px;border-radius:50%;background:#D4A017;
                        font-weight:bold;color:#fff;font-size:1rem;margin-left:.8rem;">H</div>
            <span class="brand-text font-weight-bold ml-2" style="color:#D4A017;font-size:1rem;">HT ភ្នាក់ Admin</span>
        </a>

        <div class="sidebar">
            {{-- User panel --}}
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
                 style="border-bottom:1px solid rgba(255,255,255,0.15);">
                <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:35px;height:35px;border-radius:50%;background:#D4A017;
                            font-weight:bold;color:#fff;font-size:1rem;">
                    {{ strtoupper(substr(auth()->user()->username ?? auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="info ml-2 overflow-hidden">
                    <a href="{{ route('account') }}" class="d-block font-weight-bold text-truncate" style="color:#D4A017;">
                        {{ auth()->user()->name ?? auth()->user()->username }}
                    </a>
                    <small style="color:rgba(255,255,255,0.65);">Administrator</small>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="mt-1">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                    @php
                        $navItems = [
                            ['route' => 'home',    'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
                            ['route' => 'record',  'icon' => 'fas fa-book-open',      'label' => 'Records'],
                            ['route' => 'result',  'icon' => 'fas fa-trophy',         'label' => 'Results'],
                            ['route' => 'report',  'icon' => 'fas fa-chart-bar',      'label' => 'Reports'],
                            ['route' => 'account', 'icon' => 'fas fa-user-circle',    'label' => 'Account'],
                        ];
                    @endphp

                    @foreach($navItems as $item)
                    <li class="nav-item">
                        <a href="{{ route($item['route']) }}"
                           class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                            <i class="nav-icon {{ $item['icon'] }}"></i>
                            <p>{{ $item['label'] }}</p>
                        </a>
                    </li>
                    @endforeach

                    {{-- Settings tree --}}
                    <li class="nav-item {{ request()->routeIs('setting*') ? 'menu-open' : '' }}">
                        <a href="{{ route('setting') }}"
                           class="nav-link {{ request()->routeIs('setting*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Settings <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('setting') }}"
                                   class="nav-link {{ request()->routeIs('setting') ? 'active' : '' }}"
                                   style="padding-left:2.5rem;">
                                    <i class="nav-icon fas fa-print"></i>
                                    <p>Printer &amp; Commission</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('setting.bet_time_settings') }}"
                                   class="nav-link {{ request()->routeIs('setting.bet_time_settings') ? 'active' : '' }}"
                                   style="padding-left:2.5rem;">
                                    <i class="nav-icon fas fa-clock"></i>
                                    <p>Bet Time Settings</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('setting.bet_categories') }}"
                                   class="nav-link {{ request()->routeIs('setting.bet_categories') ? 'active' : '' }}"
                                   style="padding-left:2.5rem;">
                                    <i class="nav-icon fas fa-tags"></i>
                                    <p>Bet Categories</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </nav>
        </div>
    </aside>

    {{-- ========== CONTENT WRAPPER ========== --}}
    <div class="content-wrapper" style="background-color:#f4f6f9;">

        {{-- Content header / breadcrumb --}}
        <div class="content-header" style="border-bottom:1px solid #dee2e6;">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h5 class="m-0 font-weight-bold" style="color:#DC143C;">
                            {{ $pageTitle ?? ($title ?? 'Dashboard') }}
                        </h5>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right mb-0 bg-transparent">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}" style="color:#D4A017;">Home</a>
                            </li>
                            @isset($breadcrumb)
                            <li class="breadcrumb-item active">{{ $breadcrumb }}</li>
                            @endisset
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-3 mt-3">
            <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        </div>
        @endif
        @if(session('error'))
        <div class="mx-3 mt-3">
            <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        </div>
        @endif

        {{-- Main content --}}
        <section class="content pt-3 pb-4">
            <div class="container-fluid">
                {{ $slot }}
            </div>
        </section>
    </div>

    {{-- Footer --}}
    <footer class="main-footer text-sm">
        <strong>HT ភ្នាក់</strong> &copy; {{ date('Y') }} Administrator Panel
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>

    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>

@livewireScripts
@stack('scripts')
</body>
</html>
