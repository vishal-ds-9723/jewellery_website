<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow">
  <title>@yield('title', 'Enquiries') | R.A. Parikh</title>
  <link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('admin.css') }}">
</head>
<body>
<header class="admin-header">
  <a href="{{ route('home') }}">R.A.PARIKH <small>JEWELLERS</small></a>
  @auth
    <form action="{{ route('logout') }}" method="post">@csrf<button type="submit">Sign out</button></form>
  @endauth
</header>
<main>
  @if(session('success')) <p class="notice" role="status">{{ session('success') }}</p> @endif
  @if($errors->any())
    <ul class="errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  @endif
  @yield('content')
</main>
</body>
</html>
