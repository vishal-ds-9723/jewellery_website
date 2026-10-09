@extends('admin.layout')
@section('content')
<h1>Appointment enquiries</h1>
<p>Preferred appointment dates and slots use India time. Confirm availability with the customer.</p>
<form class="filters" action="{{ route('admin.index') }}" method="get">
  <label>Status<select name="status"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
  <label>Appointment date<input type="date" name="date" value="{{ request('date') }}"></label>
  <button type="submit">Filter</button>
  <a href="{{ route('admin.index') }}">Clear</a>
  <a class="button" href="{{ route('admin.enquiries.export', request()->only('status', 'date')) }}">Export enquiries CSV</a>
</form>
<p>CSV exports include up to {{ number_format($exportLimit) }} records. Filter by date or status for larger lists.</p>
<p>Showing {{ $enquiries->count() }} enquiries</p>
<div class="table-wrap">
<table>
  <thead><tr><th>Customer</th><th>Appointment</th><th>Enquiry</th><th>Status</th><th>Received</th></tr></thead>
  <tbody>
  @forelse($enquiries as $enquiry)
    <tr>
      <td><strong>{{ $enquiry->name }}</strong><br>{{ $enquiry->email }}<br>{{ $enquiry->phone }}</td>
      <td>{{ $enquiry->appointment_date->format('d M Y') }}<br>{{ ucfirst($enquiry->appointment_slot) }}</td>
      <td>{{ ucfirst($enquiry->shopping_interest ?? 'General') }}<details><summary>Read message</summary><p class="message">{{ $enquiry->message }}</p></details></td>
      <td>
        <form action="{{ route('admin.enquiries.update', $enquiry) }}" method="post">
          @csrf @method('PATCH')
          <label class="sr-only" for="status-{{ $enquiry->id }}">Enquiry status</label>
          <select id="status-{{ $enquiry->id }}" name="status">@foreach($statuses as $status)<option value="{{ $status }}" @selected($enquiry->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
          <button type="submit">Save</button>
        </form>
      </td>
      <td>{{ $enquiry->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i a') }}</td>
    </tr>
  @empty
    <tr><td colspan="5">No enquiries found.</td></tr>
  @endforelse
  </tbody>
</table>
</div>
@include('admin.pagination', ['paginator' => $enquiries])

<h2>Newsletter subscribers</h2>
<p>Showing {{ $subscribers->count() }} signups</p>
<div class="table-wrap">
<table>
  <thead><tr><th>Email</th><th>Signed up (India time)</th></tr></thead>
  <tbody>@forelse($subscribers as $subscriber)<tr><td>{{ $subscriber->email }}</td><td>{{ $subscriber->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i a') }}</td></tr>@empty<tr><td colspan="2">No subscribers yet.</td></tr>@endforelse</tbody>
</table>
</div>
@include('admin.pagination', ['paginator' => $subscribers])
@endsection
