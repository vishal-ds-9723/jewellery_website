<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @if(request()->routeIs('home', 'contact'))
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @endif
  <meta name="asset-base" content="{{ rtrim(asset(''), '/') }}/">
  <meta name="description" content="@yield('description', 'R.A. Parikh Jewellers in Billimora.')">
  <title>@yield('title', 'R.A. Parikh Jewellers')</title>
  <link rel="canonical" href="{{ url()->current() }}">
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
  @stack('styles')
  <link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/forms.css') }}">
  <link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/responsive.css') }}">
</head>
<body class="@yield('bodyClass')">
  @unless(View::hasSection('hideNavigation'))
    @include('partials.header')
  @endunless
  @yield('content')
  @unless(View::hasSection('hideNavigation'))
    @include('partials.footer')
  @endunless
  <script src="{{ \App\Support\SiteAsset::url('js/navigation.js') }}" defer></script>
  @stack('scripts')
  <script src="{{ \App\Support\SiteAsset::url('js/forms.js') }}" defer></script>
</body>
</html>
