@extends('layouts.app')

@section('content')
@include('partials.payment-theme')

<div class="pp-wrap">

  <h1 class="pp-title"><i class="fas fa-cog"></i>&nbsp; Payment details</h1>
  <p class="pp-sub">{{ $school->name ?? '' }} · Shown to parents on the manual payment page.</p>

  @if(session('success'))
    <div class="pp-alert pp-alert-success"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>
  @endif

  <div class="pp-card">
    <div class="pp-card-body">
      <form action="{{ route('admin.payment.details.upsert', $school->id) }}" method="POST">
        @csrf

        <fieldset class="pp-fieldset">
          <legend class="pp-legend">Bank transfer</legend>
          <div class="pp-row2">
            <div class="pp-field">
              <label class="pp-label">Bank name</label>
              <input type="text" name="bank_name" class="pp-input" value="{{ old('bank_name', $paymentDetail->bank_name ?? '') }}">
            </div>
            <div class="pp-field">
              <label class="pp-label">Account name</label>
              <input type="text" name="bank_account_name" class="pp-input" value="{{ old('bank_account_name', $paymentDetail->bank_account_name ?? '') }}">
            </div>
          </div>
          <div class="pp-row2">
            <div class="pp-field">
              <label class="pp-label">Account number</label>
              <input type="text" name="bank_account_number" class="pp-input" value="{{ old('bank_account_number', $paymentDetail->bank_account_number ?? '') }}">
            </div>
            <div class="pp-field">
              <label class="pp-label">Branch</label>
              <input type="text" name="bank_branch" class="pp-input" value="{{ old('bank_branch', $paymentDetail->bank_branch ?? '') }}">
            </div>
          </div>
          <div class="pp-field">
            <label class="pp-label">Swift code (optional)</label>
            <input type="text" name="bank_swift_code" class="pp-input" value="{{ old('bank_swift_code', $paymentDetail->bank_swift_code ?? '') }}">
          </div>
        </fieldset>

        <fieldset class="pp-fieldset">
          <legend class="pp-legend">Mobile money</legend>
          <div class="pp-row2">
            <div class="pp-field">
              <label class="pp-label">Provider</label>
              <input type="text" name="mobile_money_provider" class="pp-input" placeholder="MTN, Airtel, Zamtel..." value="{{ old('mobile_money_provider', $paymentDetail->mobile_money_provider ?? '') }}">
            </div>
            <div class="pp-field">
              <label class="pp-label">Number</label>
              <input type="text" name="mobile_money_number" class="pp-input" value="{{ old('mobile_money_number', $paymentDetail->mobile_money_number ?? '') }}">
            </div>
          </div>
          <div class="pp-field">
            <label class="pp-label">Registered account name</label>
            <input type="text" name="mobile_money_account_name" class="pp-input" value="{{ old('mobile_money_account_name', $paymentDetail->mobile_money_account_name ?? '') }}">
          </div>
        </fieldset>

        <fieldset class="pp-fieldset">
          <legend class="pp-legend">Instructions for parents</legend>
          <div class="pp-field">
            <textarea name="payment_instructions" class="pp-textarea" placeholder="e.g. Include the pupil's full name as the payment reference.">{{ old('payment_instructions', $paymentDetail->payment_instructions ?? '') }}</textarea>
          </div>
        </fieldset>

        <button type="submit" class="pp-btn pp-btn-primary"><i class="fas fa-save"></i> Save payment details</button>
      </form>
    </div>
  </div>

</div>
@endsection