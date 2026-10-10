<header class="site-header {{ request()->routeIs('contact') ? 'scrolled' : '' }}" id="top">
  <a class="wordmark" href="{{ route('home') }}" aria-label="R.A. Parikh Jewellers home">
    <img class="wordmark-logo" src="{{ asset('assets/ra-parikh-logo-2-transparent.png') }}" width="2170" height="725" alt="" decoding="async">
  </a>
  <nav class="main-nav" id="main-navigation" aria-label="Main navigation">
    <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
    <a href="{{ route('collections') }}" @if(request()->routeIs('collections')) aria-current="page" @endif>Collections</a>
    <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>Our story</a>
    <a class="bag-link" href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif>Contact</a>
    <a class="appointment-cta" href="{{ route('contact') }}?appointment=video-call#enquiry-form"><span>Video Call Appointment</span></a>
  </nav>
  <div class="header-actions">
    <button class="menu-button" aria-controls="main-navigation" type="button" aria-label="Open menu" aria-expanded="false"><span></span><span></span></button>
  </div>
</header>
