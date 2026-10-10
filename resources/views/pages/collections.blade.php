@extends('layouts.site')
@section('title', 'Collections | R.A. Parikh Jewellers')
@section('description', 'Explore bridal, signature, and everyday jewellery collections by R.A. Parikh Jewellers.')
@section('bodyClass', 'collections-page')
@push('styles')
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/collections.css') }}">
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/shared.css') }}">
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/collection-focus.css') }}">
@endpush
@section('content')
<main>
    <section class="collections-hero" aria-labelledby="collections-title">
      <div class="hero-copy reveal">
        <p class="eyebrow">The complete edit</p>
        <h1 id="collections-title">Jewels for every<br><em>chapter.</em></h1>
        <p>Discover pieces shaped by Indian craft and designed to become part of your story.</p>
      </div>
      <a class="scroll-mark" href="#collection-grid" aria-label="View all collections">↓</a>
    </section>

    <section class="catalogue" id="collection-grid" aria-labelledby="catalogue-title">
      <div class="catalogue-heading reveal">
        <div><p class="eyebrow">All collections</p><h2 id="catalogue-title">Tap to explore<br><em>our journey.</em></h2></div>
        
      </div>

      <div class="collection-grid">
        <article class="product-card reveal" data-category="bridal"><button class="product-image tall" type="button" aria-pressed="false" aria-label="The Noor Set: show jewellery spotlight"><img class="product-image-base" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-1.jpeg') }}" alt="Woman wearing the Noor bridal set"><img class="product-image-focus" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-1-focus.png') }}" alt="" aria-hidden="true"></button><div class="product-meta"><div><p>Bridal edit</p><h3>The Noor Set</h3></div><span>From ₹1,85,000</span></div></article>
        <article class="product-card reveal" data-category="signature"><button class="product-image" type="button" aria-pressed="false" aria-label="Gul Jhumka: show jewellery spotlight"><img class="product-image-base" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-2.jpeg') }}" alt="Woman wearing Gul Jhumka jewellery"><img class="product-image-focus" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-2-focus.png') }}" alt="" aria-hidden="true"></button><div class="product-meta"><div><p>Signature</p><h3>Gul Jhumka</h3></div><span>From ₹48,000</span></div></article>
        <article class="product-card reveal" data-category="everyday"><button class="product-image" type="button" aria-pressed="false" aria-label="Meher Necklace: show jewellery spotlight"><img class="product-image-base" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-3.jpeg') }}" alt="Woman wearing the Meher necklace"><img class="product-image-focus" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-3-focus.png') }}" alt="" aria-hidden="true"></button><div class="product-meta"><div><p>Everyday heirloom</p><h3>Meher Necklace</h3></div><span>From ₹72,000</span></div></article>
        <article class="product-card reveal" data-category="signature"><button class="product-image" type="button" aria-pressed="false" aria-label="Sitara Jhumka: show jewellery spotlight"><img class="product-image-base" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-4.jpeg') }}" alt="Woman wearing Sitara jewellery"><img class="product-image-focus" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-4-focus.png') }}" alt="" aria-hidden="true"></button><div class="product-meta"><div><p>Signature</p><h3>Sitara Jhumka</h3></div><span>From ₹52,000</span></div></article>
        <article class="product-card reveal" data-category="bridal"><button class="product-image tall" type="button" aria-pressed="false" aria-label="The Rani Set: show jewellery spotlight"><img class="product-image-base" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-5.jpeg') }}" alt="Woman wearing the Rani bridal set"><img class="product-image-focus" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-5-focus.png') }}" alt="" aria-hidden="true"></button><div class="product-meta"><div><p>Bridal edit</p><h3>The Rani Set</h3></div><span>From ₹2,10,000</span></div></article>
        <article class="product-card reveal" data-category="everyday"><button class="product-image" type="button" aria-pressed="false" aria-label="Nira Pendant: show jewellery spotlight"><img class="product-image-base" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-6.jpeg') }}" alt="Woman wearing the Nira pendant"><img class="product-image-focus" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-6-focus.png') }}" alt="" aria-hidden="true"></button><div class="product-meta"><div><p>Everyday heirloom</p><h3>Nira Pendant</h3></div><span>From ₹42,000</span></div></article>
        <article class="product-card reveal" data-category="signature"><button class="product-image" type="button" aria-pressed="false" aria-label="Zoya Drops: show jewellery spotlight"><img class="product-image-base" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-7.jpeg') }}" alt="Woman wearing Zoya jewellery"><img class="product-image-focus" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-7-focus.png') }}" alt="" aria-hidden="true"></button><div class="product-meta"><div><p>Signature</p><h3>Zoya Drops</h3></div><span>From ₹58,000</span></div></article>
        <article class="product-card reveal" data-category="mens"><button class="product-image" type="button" aria-pressed="false" aria-label="Royal Rope Chain: show jewellery spotlight"><img class="product-image-base" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-man-1.jpeg') }}" alt="Man wearing the Royal Rope gold chain and bracelet"><img class="product-image-focus" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-man-1-focus.png') }}" alt="" aria-hidden="true"></button><div class="product-meta"><div><p>Men's edit</p><h3>Royal Rope Chain</h3></div></div></article>
        <article class="product-card reveal" data-category="mens"><button class="product-image" type="button" aria-pressed="false" aria-label="Signature Link Chain: show jewellery spotlight"><img class="product-image-base" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-man-2.jpeg') }}" alt="Man wearing the Signature Link gold chain and bracelet"><img class="product-image-focus" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-man-2-focus.png') }}" alt="" aria-hidden="true"></button><div class="product-meta"><div><p>Men's edit</p><h3>Signature Link Chain</h3></div></div></article>
        <article class="product-card reveal" data-category="mens"><button class="product-image" type="button" aria-pressed="false" aria-label="Classic Gold Chain: show jewellery spotlight"><img class="product-image-base" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-man-3.jpeg') }}" alt="Man wearing the Classic Gold chain and wrist jewellery"><img class="product-image-focus" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-man-3-focus.png') }}" alt="" aria-hidden="true"></button><div class="product-meta"><div><p>Men's edit</p><h3>Classic Gold Chain</h3></div></div></article>
        <article class="product-card reveal" data-category="mens"><button class="product-image" type="button" aria-pressed="false" aria-label="Heritage Link Chain: show jewellery spotlight"><img class="product-image-base" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-man-4.jpeg') }}" alt="Man wearing the Heritage Link gold chain and bracelet"><img class="product-image-focus" decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-man-4-focus.png') }}" alt="" aria-hidden="true"></button><div class="product-meta"><div><p>Men's edit</p><h3>Heritage Link Chain</h3></div></div></article>
        @foreach (array_diff(range(1, 15), [7, 9, 11]) as $design)
        <article class="product-card jewellery-set-card reveal" data-category="everyday">
          <button class="product-image" type="button" data-background-toggle aria-pressed="false" aria-label="Jewellery design {{ $design }}: show dark background">
            <img decoding="async" loading="lazy" src="{{ asset('jewellery-set-images/'.$design.'.png') }}" alt="Jewellery design {{ $design }}">
          </button>
        </article>
        @endforeach
      </div>
    </section>

    <section class="collection-cta">
      <div class="reveal"><p class="eyebrow">A private viewing</p><h2>See it.<br><em>Feel it.</em></h2><p>Visit the house and let us help you find a piece that feels entirely yours.</p><a href="{{ route('contact') }}">Book an appointment <span>↗</span></a></div>
    </section>
  </main>
@endsection
@push('scripts')
<script src="{{ \App\Support\SiteAsset::url('js/collections.js') }}"></script>
@endpush
