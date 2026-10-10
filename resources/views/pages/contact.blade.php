@extends('layouts.site')
@section('title', 'Contact | R.A. Parikh Jewellers')
@section('description', 'Contact R.A. Parikh Jewellers for jewellery enquiries and private appointments.')
@section('bodyClass', 'contact-page')
@push('styles')
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/contact.css') }}">
<link rel="stylesheet" href="{{ \App\Support\SiteAsset::url('css/shared.css') }}">
@endpush
@section('content')
<main>
    <section class="contact-layout" aria-labelledby="contact-title">
      <div class="contact-visual">
        <img src="{{ asset('images/bridal-set.png') }}" alt="Handcrafted gold bridal necklace and earrings">
        <div class="image-caption"><span></span><span>Bilimora (E)</span></div>
      </div>

      <div class="enquiry-panel">
        <div class="form-heading">
          <h1 id="contact-title">Now you can pre-book your <em>video call slot.</em></h1>
          <p>Our team will get in touch to guide you personally.</p>
        </div>

        <form class="enquiry-form" id="enquiry-form" action="{{ route('enquiries.store') }}" method="post" data-ajax-form data-status="enquiry-status">
          @include('partials.form-protection')
          <input type="hidden" name="submission_id" value="{{ old('submission_id', (string) Illuminate\Support\Str::uuid()) }}">
          @if($errors->any())<ul class="server-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
          <div class="field-row">
            <div class="field"><label for="name">Name</label><input id="name" name="name" maxlength="100" value="{{ old('name') }}" type="text" autocomplete="name" required></div>
            <div class="field"><label for="email">Email</label><input id="email" name="email" maxlength="255" value="{{ old('email') }}" type="email" autocomplete="email" required></div>
          </div>
          <div class="field-row">
            <div class="field"><label for="phone">Contact number</label><input id="phone" name="phone" maxlength="30" value="{{ old('phone') }}" type="tel" autocomplete="tel" inputmode="tel" required></div>
            <div class="field"><label for="shopping-interest">What are you looking for?</label><select id="shopping-interest" name="shopping_interest"><option value="" selected disabled>Select an option</option><option value="men" @selected(old('shopping_interest') === 'men')>Men</option><option value="women" @selected(old('shopping_interest') === 'women')>Women</option><option value="gifting" @selected(old('shopping_interest') === 'gifting')>Gifting</option><option value="silver" @selected(old('shopping_interest') === 'silver')>Silver</option></select></div>
          </div>
          <div class="field-row">
            <div class="field"><label for="appointment-date">Appointment date</label><input id="appointment-date" name="appointment_date" min="{{ now('Asia/Kolkata')->toDateString() }}" value="{{ old('appointment_date') }}" type="date" required></div>
            <div class="field"><label for="appointment-slot">Preferred slot</label><select id="appointment-slot" name="appointment_slot" required><option value="" selected disabled>Select a slot</option><option value="morning" @selected(old('appointment_slot') === 'morning')>Morning</option><option value="evening" @selected(old('appointment_slot') === 'evening')>Evening</option></select></div>
          </div>
          <div class="field"><label for="message">Message</label><textarea id="message" name="message" rows="4" maxlength="5000" placeholder="How can we help?" required>{{ old('message') }}</textarea></div>
          <button class="submit-button" type="submit">Submit</button>
          <p class="form-status" id="enquiry-status" role="status" aria-live="polite">{{ session('success') }}</p>
        </form>
      </div>
    </section>

    <section class="contact-details" aria-label="Contact details">
      <p>Prefer to speak directly?</p>
      <div><span>Call</span><a href="tel:+919624111899 ">+91 9624111899 </a></div>
      <div><span>Email</span><a href="mailto:hello@raparikh.com">raparikhjewellers@yahoo.co.in
</a></div>
      <div><span>Visit</span><p>Billimora (E), Gujarat</p></div>
    </section>
  </main>
@endsection
@push('scripts')
<script src="{{ \App\Support\SiteAsset::url('js/contact.js') }}"></script>
@endpush
