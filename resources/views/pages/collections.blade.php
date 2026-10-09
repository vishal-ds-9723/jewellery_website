@extends('layouts.site')
@section('title', 'Collections | R.A. Parikh Jewellers')
@section('description', 'Explore bridal, signature, and everyday jewellery collections by R.A. Parikh Jewellers.')
@section('bodyClass', 'collections-page')
@push('styles')
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/collections.css') }}">
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/shared.css') }}">
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/collection-links.css') }}">
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/mens-collection.css') }}">
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
        <div><p class="eyebrow">All collections</p><h2 id="catalogue-title">Find the one<br><em>you keep.</em></h2></div>
        <div class="filters" aria-label="Filter collections">
          <button class="active" type="button" data-filter="all">All</button>
          <button type="button" data-filter="bridal">Bridal</button>
          <button type="button" data-filter="signature">Signature</button>
          <button type="button" data-filter="everyday">Everyday</button>
        </div>
      </div>

      <div class="collection-grid">
        <article class="product-card reveal" data-category="bridal"><a href="{{ route('product') }}?item=noor"><div class="product-image tall"><img decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-1.jpeg') }}" alt="Woman wearing the Noor bridal set"></div><div class="product-meta"><div><p>Bridal edit</p><h3>The Noor Set</h3></div><span>From ₹1,85,000</span></div></a></article>
        <article class="product-card reveal" data-category="signature"><a href="{{ route('product') }}?item=gul"><div class="product-image"><img decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-2.jpeg') }}" alt="Woman wearing Gul Jhumka jewellery"></div><div class="product-meta"><div><p>Signature</p><h3>Gul Jhumka</h3></div><span>From ₹48,000</span></div></a></article>
        <article class="product-card reveal" data-category="everyday"><a href="{{ route('product') }}?item=meher"><div class="product-image"><img decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-3.jpeg') }}" alt="Woman wearing the Meher necklace"></div><div class="product-meta"><div><p>Everyday heirloom</p><h3>Meher Necklace</h3></div><span>From ₹72,000</span></div></a></article>
        <article class="product-card reveal" data-category="signature"><a href="{{ route('product') }}?item=sitara"><div class="product-image"><img decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-4.jpeg') }}" alt="Woman wearing Sitara jewellery"></div><div class="product-meta"><div><p>Signature</p><h3>Sitara Jhumka</h3></div><span>From ₹52,000</span></div></a></article>
        <article class="product-card reveal" data-category="bridal"><a href="{{ route('product') }}?item=rani"><div class="product-image tall"><img decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-5.jpeg') }}" alt="Woman wearing the Rani bridal set"></div><div class="product-meta"><div><p>Bridal edit</p><h3>The Rani Set</h3></div><span>From ₹2,10,000</span></div></a></article>
        <article class="product-card reveal" data-category="everyday"><a href="{{ route('product') }}?item=nira"><div class="product-image"><img decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-6.jpeg') }}" alt="Woman wearing the Nira pendant"></div><div class="product-meta"><div><p>Everyday heirloom</p><h3>Nira Pendant</h3></div><span>From ₹42,000</span></div></a></article>
        <article class="product-card reveal" data-category="signature"><a href="{{ route('product') }}?item=zoya"><div class="product-image"><img decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-person-7.jpeg') }}" alt="Woman wearing Zoya jewellery"></div><div class="product-meta"><div><p>Signature</p><h3>Zoya Drops</h3></div><span>From ₹58,000</span></div></a></article>
      </div>
    </section>

    <section class="mens-collection" aria-labelledby="mens-collection-title">
      <div class="mens-heading reveal">
        <p class="eyebrow">For him</p>
        <h2 id="mens-collection-title">The men's<br><em>collection.</em></h2>
        <p>Gold chains and bracelets with a strong, considered presence.</p>
      </div>

      <div class="mens-grid">
        <article class="mens-card reveal">
          <a class="mens-product-link" href="{{ route('product') }}?item=mens-rope-chain"><img decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-man-1.jpeg') }}" alt="Man wearing the Royal Rope gold chain and bracelet"><h3>Royal Rope Chain</h3></a>
        </article>
        <article class="mens-card reveal">
          <a class="mens-product-link" href="{{ route('product') }}?item=mens-signature-chain"><img decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-man-2.jpeg') }}" alt="Man wearing the Signature Link gold chain and bracelet"><h3>Signature Link Chain</h3></a>
        </article>
        <article class="mens-card reveal">
          <a class="mens-product-link" href="{{ route('product') }}?item=mens-classic-chain"><img decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-man-3.jpeg') }}" alt="Man wearing the Classic Gold chain and wrist jewellery"><h3>Classic Gold Chain</h3></a>
        </article>
        <article class="mens-card reveal">
          <a class="mens-product-link" href="{{ route('product') }}?item=mens-heritage-chain"><img decoding="async" loading="lazy" src="{{ asset('assets/ra-parekh-man-4.jpeg') }}" alt="Man wearing the Heritage Link gold chain and bracelet"><h3>Heritage Link Chain</h3></a>
        </article>
        <article class="mens-card mens-video-card reveal">
          <video controls playsinline preload="metadata" aria-label="Men's jewellery video 01">
            <source src="{{ asset('assets/ra-parekh-man-4-video.mp4') }}" type="video/mp4">
            Your browser does not support video playback.
          </video>
          <h3>Men's Jewellery Film 01</h3>
        </article>
        <article class="mens-card mens-video-card reveal">
          <video controls playsinline preload="metadata" aria-label="Men's jewellery video 02">
            <source src="{{ asset('assets/ra-parekh-man-4-video-2.mp4') }}" type="video/mp4">
            Your browser does not support video playback.
          </video>
          <h3>Men's Jewellery Film 02</h3>
        </article>
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
