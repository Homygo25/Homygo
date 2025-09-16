<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-pwa="true">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">

    <!-- SEO Meta Tags -->
    <title>Finder | @yield('title')</title>
    <meta name="description" content="Finder - Directory & Listings Bootstrap HTML Template">
    <meta name="keywords" content="directory, listings, search, car dealer, real estate, city guide, business listings, medical directories, event listings, e-commerce, market, multipurpose, ui kit, light and dark mode, bootstrap, html5, css3, javascript, gallery, slider, mobile, pwa">
    <meta name="author" content="Createx Studio">

    <!-- Webmanifest + Favicon / App icons -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
  
    <link rel="icon" type="image/png" href="build/app-icons/icon-32x32.png" sizes="32x32">
    <link rel="apple-touch-icon" href="build/app-icons/icon-180x180.png">

  @yield('css')

  @include('partials/head-css')
</head>

<body class="bg-body-tertiary">

    @yield('content')

    @include('partials.vendor-scripts')

    @yield('scripts')

    </body>

</html>