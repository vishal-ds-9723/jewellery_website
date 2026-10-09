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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
  @stack('styles')
  <link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/forms.css') }}">
</head>
<body class="@yield('bodyClass')">
  @unless(View::hasSection('hideNavigation'))
    @include('partials.header')
  @endunless
  @yield('content')
  @unless(View::hasSection('hideNavigation'))
    @include('partials.footer')
  @endunless
  @stack('scripts')
  <script src="{{ \App\Support\SiteAsset::url('js/forms.js') }}" defer></script>
</body>
</html>
