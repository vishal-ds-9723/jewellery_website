@extends('layouts.site')
@section('title', 'About Us | R.A. Parikh Jewellers')
@section('description', 'Discover R.A. Parikh Jewellers in Billimora: traditional and contemporary jewellery, expert-checked quality, and transparent prices and policies.')
@section('bodyClass', 'about-page')
@push('styles')
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('about.css') }}">
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('shared.css') }}">
@endpush
@section('content')
<main>
    <section class="intro-section section-shell" id="beginning">
      <div class="intro-left reveal">
        <p class="overline">The house behind the jewel</p>
        <h1>About<br><em>Us.</em></h1>
        <p class="intro-text">The journey of R.A. Parikh Jewellers began with a small outlet in Billimora offering traditional jewellery. After the success of this store, we opened a spacious showroom with traditional as well as trendy designs, becoming a preferred choice for Indian ethnic fashion and setting new standards of customer delight every day.</p>
        <a class="arrow-link" href="#our-collection">Discover our collection <span>&darr;</span></a>
      </div>
      <div class="intro-right reveal">
        <img class="about-brand-logo" src="{{ asset('assets/ra-parikh-logo-2-transparent.png') }}" width="2170" height="725" alt="R.A. Parikh Jewellers - Antalia" decoding="async">
      </div>
    </section>

    <section class="origin-section section-shell" id="our-collection">
      <div class="origin-image reveal"><img decoding="async" loading="lazy" src="{{ asset('images/heritage-atelier.png') }}" alt="An artisan carefully working on a gold necklace"></div>
      <div class="origin-copy reveal">
        <p class="overline">Our collection</p>
        <h2>For every mood.<br><em>Every occasion.</em></h2>
        <p>We have designs to suit every mood, budget, and occasion. Our wide collection includes necklaces, rings, earrings, bracelets, wedding sets, watches, and many more pieces for everyday wear and special occasions.</p>
        <p>Visit our showroom, where our customer service team will be happy to help you choose a design from our wide collection of fine jewellery, whether for men, women, or newborn babies.</p>
        <div class="origin-signature">R.A. Parikh <span>&times;</span> Billimora</div>
      </div>
    </section>

    <section class="beliefs-section section-shell" id="beliefs">
      <div class="beliefs-heading reveal"><p class="overline">Our commitment</p><h2>Quality, care,<br><em>and trust.</em></h2></div>
      <div class="belief-list">
        <article class="belief belief-one reveal"><span class="belief-number">01</span><div><h3>Quality comes first</h3><p>We understand that quality matters most to each one of you. Every piece of jewellery is crafted with utmost care.</p></div><b aria-hidden="true">&nearr;</b></article>
        <article class="belief belief-two reveal"><span class="belief-number">02</span><div><h3>Checked with care</h3><p>Every piece of jewellery is thoroughly checked and certified by an expert.</p></div><b aria-hidden="true">&nearr;</b></article>
        <article class="belief belief-three reveal"><span class="belief-number">03</span><div><h3>Shop with confidence</h3><p>We are open in our interactions with our customers. Our prices and policies are always transparent, so you can shop with confidence and convenience.</p></div><b aria-hidden="true">&nearr;</b></article>
      </div>
    </section>

    <section class="closing-section">
      <div class="closing-orbit" aria-hidden="true"><span></span><span></span><span></span></div>
      <div class="closing-content reveal"><p class="overline">Visit our showroom</p><h2>Find a piece<br><em>for you.</em></h2><p>Explore our collection in Billimora and let our team help you choose a design for your occasion and budget.</p><a class="closing-button" href="https://maps.app.goo.gl/yTkJx2cxSU24KZjD7"  target="_blank">Visit our Showroom</a></div>
      <p class="closing-side-note">For everyday wear.<br>For special occasions.</p>
    </section>
  </main>
@endsection
@push('scripts')
<script src="{{ \App\Support\SiteAsset::url('about.js') }}"></script>
@endpush
