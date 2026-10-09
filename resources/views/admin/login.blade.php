@extends('admin.layout')
@section('title', 'Administrator sign in')
@section('content')
<section class="login-card">
  <h1>Administrator sign in</h1>
  <p>Manage appointment enquiries and newsletter signups.</p>
  <form action="{{ route('login.store') }}" method="post">
    @csrf
    <label for="email">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
    <label for="password">Password</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required>
    <button type="submit">Sign in</button>
  </form>
</section>
@endsection
