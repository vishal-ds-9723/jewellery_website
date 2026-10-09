@extends('layouts.site')
@section('title', 'R.A.PARIKH JEWELLERS | Made to become a story')
@section('description', 'Aavya Fine Jewellery - contemporary heirlooms, made for the moments that become family stories.')
@section('bodyClass', 'home-page')
@push('styles')
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/styles.css') }}">
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/shared.css') }}">
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/home-collection.css') }}">
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/hero-showroom.css') }}">
@endpush
@section('content')
<main>
    <section class="hero showroom-hero" aria-label="R.A. Parikh Jewellers showroom">
      <div class="showroom-scene" id="showroom-scene">
        <div class="showroom-images">
          <img class="showroom-day" src="{{ asset('assets/showroom-landscape.png') }}" width="1672" height="941" alt="R.A. Parikh Jewellers showroom in Billimora, with gold detailing and a grand columned entrance" fetchpriority="high" decoding="async">
          <img class="showroom-gold" src="{{ asset('assets/showroom-landscape-dark-gold-lanterns.png') }}" width="1672" height="941" alt="" aria-hidden="true" decoding="async">
        </div>
      </div>
      <div class="showroom-copy">
        <h1>A home for<br><em>timeless<br>jewellery.</em></h1>
        <p>Discover timeless designs, crafted to become part of your story.</p>
      </div>
    </section>

    <section class="collection-section section-pad" id="collections" aria-labelledby="collection-title">
      <div class="section-heading reveal">
        <p class="eyebrow">The Sets</p>
        <h2 id="collection-title">Pieces for your<br><em>most remembered</em> days.</h2>
        <a class="text-link" href="{{ route('collections') }}"><span>View all pieces</span><span>↗</span></a>
      </div>
      <div class="collection-grid" data-horizontal-scroll tabindex="0" aria-label="Jewellery collections. Scroll horizontally to explore.">
        <article class="product-card product-card-tall reveal">
          <div class="product-image"><img decoding="async" loading="lazy" class="product-image-base" src="{{ asset('assets/ra-parekh-person-1.jpeg') }}" alt="Woman wearing the ornate Noor bridal necklace and earrings"><img decoding="async" loading="lazy" class="product-image-focus" src="{{ asset('assets/ra-parekh-person-1-focus.png') }}" alt="" aria-hidden="true"></div>
          <div class="product-meta"><div><p class="product-kicker">Bridal edit</p><h3>The Noor Set</h3></div><p>From ₹1,85,000</p></div>
        </article>
        <article class="product-card reveal">
          <div class="product-image product-image-detail"><img decoding="async" loading="lazy" class="product-image-base" src="{{ asset('assets/ra-parekh-person-2.jpeg') }}" alt="Woman wearing ornate gold and ruby Gul Jhumka earrings"><img decoding="async" loading="lazy" class="product-image-focus" src="{{ asset('assets/ra-parekh-person-2-focus.png') }}" alt="" aria-hidden="true"></div>
          <div class="product-meta"><div><p class="product-kicker">Signature</p><h3>Gul Jhumka</h3></div><p>From ₹48,000</p></div>
        </article>
        <article class="product-card product-card-offset reveal">
          <div class="product-image product-image-model"><img decoding="async" loading="lazy" class="product-image-base" src="{{ asset('assets/ra-parekh-person-3.jpeg') }}" alt="Woman wearing the delicate gold and emerald Meher Necklace"><img decoding="async" loading="lazy" class="product-image-focus" src="{{ asset('assets/ra-parekh-person-3-focus.png') }}" alt="" aria-hidden="true"></div>
          <div class="product-meta"><div><p class="product-kicker">Everyday heirloom</p><h3>Meher Necklace</h3></div><p>From ₹72,000</p></div>
        </article>
        <article class="product-card reveal">
          <div class="product-image"><img decoding="async" loading="lazy" class="product-image-base" src="{{ asset('assets/ra-parekh-person-4.jpeg') }}" alt="Woman wearing the Sitara gold earrings"><img decoding="async" loading="lazy" class="product-image-focus" src="{{ asset('assets/ra-parekh-person-4-focus.png') }}" alt="" aria-hidden="true"></div>
          <div class="product-meta"><div><p class="product-kicker">Signature</p><h3>Sitara Jhumka</h3></div><p>From ₹52,000</p></div>
        </article>
        <article class="product-card reveal">
          <div class="product-image"><img decoding="async" loading="lazy" class="product-image-base" src="{{ asset('assets/ra-parekh-person-5.jpeg') }}" alt="Woman wearing the Rani bridal jewellery set"><img decoding="async" loading="lazy" class="product-image-focus" src="{{ asset('assets/ra-parekh-person-5-focus.png') }}" alt="" aria-hidden="true"></div>
          <div class="product-meta"><div><p class="product-kicker">Bridal edit</p><h3>The Rani Set</h3></div><p>From ₹2,10,000</p></div>
        </article>
        <article class="product-card reveal">
          <div class="product-image"><img decoding="async" loading="lazy" class="product-image-base" src="{{ asset('assets/ra-parekh-person-6.jpeg') }}" alt="Woman wearing the Nira gold pendant"><img decoding="async" loading="lazy" class="product-image-focus" src="{{ asset('assets/ra-parekh-person-6-focus.png') }}" alt="" aria-hidden="true"></div>
          <div class="product-meta"><div><p class="product-kicker">Everyday heirloom</p><h3>Nira Pendant</h3></div><p>From ₹42,000</p></div>
        </article>
        <article class="product-card reveal">
          <div class="product-image"><img decoding="async" loading="lazy" class="product-image-base" src="{{ asset('assets/ra-parekh-person-7.jpeg') }}" alt="Woman wearing the Zoya statement earrings"><img decoding="async" loading="lazy" class="product-image-focus" src="{{ asset('assets/ra-parekh-person-7-focus.png') }}" alt="" aria-hidden="true"></div>
          <div class="product-meta"><div><p class="product-kicker">Signature</p><h3>Zoya Drops</h3></div><p>From ₹58,000</p></div>
        </article>
        <article class="product-card reveal">
          <div class="product-image"><img decoding="async" loading="lazy" class="product-image-base" src="{{ asset('assets/ra-parekh-man-1.jpeg') }}" alt="Man wearing the Royal Rope gold chain and bracelet"><img decoding="async" loading="lazy" class="product-image-focus" src="{{ asset('assets/ra-parekh-man-1-focus.png') }}" alt="" aria-hidden="true"></div>
          <div class="product-meta"><div><p class="product-kicker">Men's edit</p><h3>Royal Rope Chain</h3></div></div>
        </article>
        <article class="product-card reveal">
          <div class="product-image"><img decoding="async" loading="lazy" class="product-image-base" src="{{ asset('assets/ra-parekh-man-2.jpeg') }}" alt="Man wearing the Signature Link gold chain and bracelet"><img decoding="async" loading="lazy" class="product-image-focus" src="{{ asset('assets/ra-parekh-man-2-focus.png') }}" alt="" aria-hidden="true"></div>
          <div class="product-meta"><div><p class="product-kicker">Men's edit</p><h3>Signature Link Chain</h3></div></div>
        </article>
        <article class="product-card reveal">
          <div class="product-image"><img decoding="async" loading="lazy" class="product-image-base" src="{{ asset('assets/ra-parekh-man-3.jpeg') }}" alt="Man wearing the Classic Gold chain and wrist jewellery"><img decoding="async" loading="lazy" class="product-image-focus" src="{{ asset('assets/ra-parekh-man-3-focus.png') }}" alt="" aria-hidden="true"></div>
          <div class="product-meta"><div><p class="product-kicker">Men's edit</p><h3>Classic Gold Chain</h3></div></div>
        </article>
        <article class="product-card reveal">
          <div class="product-image"><img decoding="async" loading="lazy" class="product-image-base" src="{{ asset('assets/ra-parekh-man-4.jpeg') }}" alt="Man wearing the Heritage Link gold chain and bracelet"><img decoding="async" loading="lazy" class="product-image-focus" src="{{ asset('assets/ra-parekh-man-4-focus.png') }}" alt="" aria-hidden="true"></div>
          <div class="product-meta"><div><p class="product-kicker">Men's edit</p><h3>Heritage Link Chain</h3></div></div>
        </article>
      </div>
      <div class="collection-scroll-controls" aria-label="Collection carousel controls">
        <button type="button" data-scroll-previous aria-label="View previous jewellery pieces" disabled><span aria-hidden="true">←</span></button>
        <button type="button" data-scroll-next aria-label="View next jewellery pieces"><span aria-hidden="true">→</span></button>
      </div>
    </section>

    <section class="story-section" id="story" aria-labelledby="story-title">
      <div class="story-image reveal"><img decoding="async" loading="lazy" src="{{ asset('images/person.png') }}" alt="Woman in a sari wearing a gold necklace"></div>
      <div class="story-copy reveal">
        <p class="eyebrow">Our story</p>
        <h2 id="story-title">Jewellery with<br><em>a little more soul.</em></h2>
        <p>Aavya began with a simple belief: the finest jewellery should feel like it has always belonged to you. We look to the old streets of Billimora, the quiet drama of a wedding morning, and the women who make every room their own.</p>
        <p>Our pieces bring the poetry of Indian craft into a modern wardrobe. Wear them often. Keep them forever. Let them gather stories.</p>
        <a class="text-link" href="{{ route('about') }}">View more <span>↗</span></a>
      </div>
    </section>

    <section class="craft-section section-pad" id="craft" aria-labelledby="craft-title">
      <div class="craft-intro reveal"><p class="eyebrow">The Aavya way</p><h2 id="craft-title">The beauty is<br><em>in the making.</em></h2></div>
      <div class="craft-list">
        <div class="craft-item reveal"><span>01</span><div><h3>Old-world techniques</h3><p>Hand-carved details, meenakari colour, and jadau settings shaped by craftspeople who know every tool by heart.</p></div><b>+</b></div>
        <div class="craft-item reveal"><span>02</span><div><h3>Considered materials</h3><p>Recycled gold, responsibly sourced stones, and the kind of quality that gets better with time.</p></div><b>+</b></div>
        <div class="craft-item reveal"><span>03</span><div><h3>Made for your chapter</h3><p>From a first celebration to a family heirloom, every piece is finished to be lived in and loved out loud.</p></div><b>+</b></div>
      </div>
    </section>

    <section class="appointment-section" aria-labelledby="appointment-title">
      <div class="appointment-content reveal"><p class="eyebrow">Come closer</p><h2 id="appointment-title">Your story,<br><em>in person.</em></h2><p>Visit our Billimora atelier for a private edit, a cup of chai, and time to find the one.</p><a class="button button-dark" href="{{ route('contact') }}">Book an appointment <span>↗</span></a></div>
    </section>

    <section class="contact-section section-pad" id="contact" aria-labelledby="contact-title">
      <div class="contact-heading reveal"><p class="eyebrow">Stay in the know</p><h2 id="contact-title">A note from<br><em>the house.</em></h2></div>
      <div class="contact-form-wrap reveal"><p>New collections, studio notes, and stories worth keeping. Delivered occasionally.</p><form id="newsletter-form" action="{{ route('newsletter.store') }}" method="post" data-ajax-form data-status="newsletter-status">@include('partials.form-protection')<label class="sr-only" for="email">Your email address</label><input id="email" name="email" maxlength="255" autocomplete="email" value="{{ old('email') }}" type="email" placeholder="Your email address" required><button type="submit" aria-label="Subscribe">↗</button></form><p class="form-status" id="newsletter-status" role="status" aria-live="polite">{{ session('success') }}@error('email') {{ $message }} @enderror</p></div>
    </section>
  </main>
<div class="toast" role="status" aria-live="polite"></div>
@endsection
@push('scripts')
<script src="{{ \App\Support\SiteAsset::url('js/hero-showroom.js') }}" defer></script>
<script src="{{ \App\Support\SiteAsset::url('js/script.js') }}"></script>
@endpush
