<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ config('app.name') }} - @yield('title', 'Admin Panel')</title>
    <!-- Meta -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon & Manifest -->
    <link rel="icon" href="{{ asset('admin/assets/images/favicon.svg') }}" type="image/x-icon" />

    <!-- Font Family -->
    <link rel="stylesheet" href="{{ asset('admin/assets/fonts/inter/inter.css') }}" id="main-font-link" />
    <!-- phosphor Icons -->
    <link rel="stylesheet" href="{{ asset('admin/assets/fonts/phosphor/duotone/style.css') }}" />
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="{{ asset('admin/assets/fonts/tabler-icons.min.css') }}" />
    <!-- Feather Icons -->
    <link rel="stylesheet" href="{{ asset('admin/assets/fonts/feather.css') }}" />
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('admin/assets/fonts/fontawesome.css') }}" />
    <!-- Material Icons -->
    <link rel="stylesheet" href="{{ asset('admin/assets/fonts/material.css') }}" />

    <!-- Template CSS Files -->
    <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}" id="main-style-link" />
    <link rel="stylesheet" href="{{ asset('admin/assets/css/style-preset.css') }}" />

    @vite(['resources/css/back.css', 'resources/js/back.js'])

    <!-- Additional CSS -->
    @stack('styles')
</head>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr" data-pc-theme_contrast="" data-pc-theme="light">

<!-- [ Pre-loader ] start -->
<div class="loader-bg">
    <div class="loader-track">
        <div class="loader-fill"></div>
    </div>
</div>
<!-- [ Pre-loader ] End -->


@include('back.layouts.sidebar')

@include('back.layouts.header')

<!-- [ Main Content ] start -->
<div class="pc-container">
    <div class="pc-content">
        @yield('content')
    </div>
</div>
<!-- [ Main Content ] end -->

@include('back.layouts.footer')

</body>
</html>

