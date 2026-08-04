@extends('layouts.app')

@section('content')
@include('partials.payment-theme')

<div class="pp-wrap">

  <h1 class="pp-title"><i class="fas fa-search"></i>&nbsp; Find your account</h1>
  <p class="pp-sub">Enter your registered phone number to view and pay school fees.</p>

  <div class="pp-card">
    <div class="pp-card-body">

      @if(session('error'))
        <div class="pp-alert pp-alert-danger">
          <i class="fas fa-exclamation-circle"></i>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      <form method="POST" action="{{ route('parent.search') }}">
        @csrf

        <div class="pp-field">
          <label class="pp-label" for="phone">Phone number</label>
          <input
            type="text"
            id="phone"
            name="phone"
            class="pp-input @error('phone') is-error @enderror"
            placeholder="e.g. 0961234567"
            inputmode="tel"
            autofocus
            value="{{ old('phone') }}"
          />
          @error('phone')
            <p class="pp-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
          @enderror
        </div>

        <button type="submit" class="pp-btn pp-btn-primary">
          <i class="fas fa-arrow-right"></i> Continue
        </button>
      </form>

    </div>
  </div>

  <p class="pp-hint" style="text-align:center;">We'll text a one-time code to this number to verify it's you.</p>

</div>
@endsection