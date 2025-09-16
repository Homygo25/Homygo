<!-- Theme switcher (color modes) -->
<script src="{{ URL::asset('build/js/theme-switcher.js') }}"></script>

<!-- Preloaded local web font (Inter) -->
<link rel="preload" href="{{ URL::asset('build/fonts/inter-variable-latin.woff2') }}" as="font" type="font/woff2" crossorigin>

<!-- Font icons -->
<link rel="preload" href="{{ URL::asset('build/icons/finder-icons.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ URL::asset('build/icons/finder-icons.min.css') }}">

<!-- Vendor styles -->


<!-- Bootstrap + Theme styles -->
<link rel="preload" href="{{ URL::asset('build/css/theme.min.css') }}" as="style">
{{-- <link rel="preload" href="{{ URL::asset('build/css/theme.rtl.css') }}" as="style"> --}}
<link rel="stylesheet" href="{{ URL::asset('build/css/theme.min.css') }}" id="theme-styles">
