@extends('layouts.app')

@section('content')
@include('partials.payment-theme')

<div class="pp-wrap">
  <div class="pp-card">
    <div class="pp-card-body" style="text-align:center;">

      <div class="pp-stamp"><i class="fas fa-clock"></i> PENDING VERIFICATION</div>

      <h1 class="pp-title" style="text-align:center;">Payment submitted</h1>
      <p class="pp-sub" style="text-align:center;">
        Thanks — we've received your payment details. The school office will verify it and apply it to the balance, usually within 1 business day.
      </p>

      @if($reference)
      <div class="pp-ref-box">
        <div class="k">Reference</div>
        <div class="v">{{ $reference }}</div>
      </div>
      @endif

      <a href="{{ route('parent.search.page') }}" class="pp-btn pp-btn-primary">
        <i class="fas fa-check"></i> Done
      </a>

    </div>
  </div>
</div>
@endsection