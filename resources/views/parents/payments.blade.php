@extends('layouts.app')

@section('content')
@include('partials.payment-theme')

<div class="pp-wrap">

  @php
    $pupilName = trim(($pupil->first_name ?? '') . ' ' . ($pupil->last_name ?? '')) ?: 'Pupil';
  @endphp
  <h1 class="pp-title"><i class="fas fa-graduation-cap"></i>&nbsp; {{ $pupilName }}'s fees</h1>
  <p class="pp-sub">{{ $pupil->school->name ?? 'School' }} · Choose how you'd like to pay for each item below.</p>

  @if(session('error'))
    <div class="pp-alert pp-alert-danger"><i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span></div>
  @endif
  @if(session('success'))
    <div class="pp-alert pp-alert-success"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>
  @endif

  @forelse($payments as $payment)
    <div class="pp-card">
      <div class="pp-card-body">
        <div class="pp-item-top">
          <div>
            <div class="pp-item-desc">{{ $payment->type ?? 'School Fees' }}</div>
            <div class="pp-item-term">{{ $payment->term ?? '' }}</div>
          </div>
          <div>
            <div class="pp-amount">K{{ number_format($payment->balance, 2) }}</div>
            <div class="pp-amount-label">balance due</div>
          </div>
        </div>

        <div class="pp-btn-row">
          <button type="button" class="pp-btn pp-btn-primary" onclick="openMomo({{ $payment->id }}, {{ $payment->balance }})">
            <i class="fas fa-bolt"></i> Pay now (instant)
          </button>
        </div>
      </div>
    </div>
  @empty
    <div class="pp-empty">
      <div class="big">Nothing due</div>
      There are no outstanding fees for this pupil.
    </div>
  @endforelse

</div>

<!-- Instant mobile money modal -->
<div class="pp-modal-overlay" id="momoOverlay">
  <div class="pp-modal">
    <h3><i class="fas fa-mobile-alt"></i> Pay with mobile money</h3>
    <form id="momoForm" method="POST">
      @csrf
      <div class="pp-field">
        <label class="pp-label" for="amount_to_pay">Amount to pay (K)</label>
        <input type="number" step="0.01" min="0.01" id="amount_to_pay" name="amount_to_pay" class="pp-input" required>
        <small class="pp-hint" id="momoBalanceHint"></small>
      </div>
      <div class="pp-field">
        <label class="pp-label" for="payment_phone">Mobile money number</label>
        <input type="text" id="payment_phone" name="payment_phone" class="pp-input" placeholder="e.g. 0961234567" required>
      </div>
      <div class="pp-field">
        <label class="pp-label" for="operator">Network</label>
        <select id="operator" name="operator" class="pp-select" required>
          <option value="">Select network</option>
          <option value="mtn">MTN</option>
          <option value="airtel">Airtel</option>
          <option value="zamtel">Zamtel</option>
        </select>
      </div>
      <div class="pp-modal-actions">
        <button type="button" class="pp-btn pp-btn-ghost" style="width:auto;" onclick="closeMomo()">Cancel</button>
        <button type="submit" class="pp-btn pp-btn-primary" style="width:auto;">Send payment prompt</button>
      </div>
    </form>
  </div>
</div>

<script>
function openMomo(paymentId, balance){
  const template = "{{ route('parent.pay', ['paymentId' => '__ID__']) }}";
  document.getElementById('momoForm').action = template.replace('__ID__', paymentId);

  const amountInput = document.getElementById('amount_to_pay');
  amountInput.max = balance;
  amountInput.value = balance;
  document.getElementById('momoBalanceHint').textContent = 'Balance due: K' + Number(balance).toFixed(2) + ' — enter a smaller amount to pay part of it.';

  document.getElementById('momoOverlay').classList.add('open');
}
function closeMomo(){
  document.getElementById('momoOverlay').classList.remove('open');
}
</script>
@endsection