@extends('layouts.site')
@section('title', 'Jewellery | R.A. Parikh Jewellers')
@section('description', 'R.A. Parikh jewellery detail')
@section('bodyClass', 'product-page')
@section('hideNavigation', 'true')
@push('styles')
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/product.css') }}">
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/shared.css') }}">
@endpush
@section('content')
<main class="product-detail">
    <a class="back-link" href="{{ route('collections') }}" aria-label="Back to collections">← Collections</a>
    <div class="product-photo">
      <button class="zoom-button" type="button" aria-label="Zoom jewellery image to 3 times" aria-pressed="false">
        <img id="product-image" src="{{ asset('assets/ra-parekh-person-1.jpeg') }}" alt="">
      </button>
    </div>
    <section class="product-copy" aria-labelledby="product-title">
      <p id="product-category" class="eyebrow"></p>
      <h1 id="product-title"></h1>
      <p id="product-description"></p>
      <a class="appointment-cta" href="{{ route('contact') }}?appointment=video-call#enquiry-form"><span>Video Call Appointment</span></a>
    </section>
  </main>
@endsection
@push('scripts')
<script src="{{ \App\Support\SiteAsset::url('js/product.js') }}"></script>
@endpush
