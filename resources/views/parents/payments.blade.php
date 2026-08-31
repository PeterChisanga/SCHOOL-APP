@extends('layouts.app')

@section('content')
@include('partials.payment-theme')

<div class="pp-wrap">

  @php
    $studentName = trim(implode(' ', array_filter([
        $pupil->first_name ?? '',
        $pupil->middle_name ?? '',
        $pupil->last_name  ?? '',
    ]))) ?: 'Student';
    $initials = strtoupper(($pupil->first_name[0] ?? '') . ($pupil->last_name[0] ?? ''));
  @endphp

  <h1 class="pp-title"><i class="fas fa-graduation-cap"></i>&nbsp; {{ $studentName }}'s fees</h1>
  <p class="pp-sub">{{ $pupil->school->name ?? 'School' }} · Choose how you'd like to pay for each item below.</p>

  <!-- Who this payment is for -->
  <div class="pp-card">
    <div class="pp-card-body">
      <div class="pp-badge" style="margin-bottom: 0;">
        <div class="pp-badge-icon">{{ $initials }}</div>
        <div class="pp-who">
          <p class="pp-badge-label">Paying for</p>
          <p class="pp-badge-value">{{ $studentName }}</p>
        </div>
      </div>
      <div class="pp-details-grid">
        <div class="item">
          <div class="k">Class</div>
          <div class="v">{{ $pupil->class->name ?? '—' }}</div>
        </div>
        <div class="item">
          <div class="k">School</div>
          <div class="v">{{ $pupil->school->name ?? '—' }}</div>
        </div>
        <div class="item">
          <div class="k">Paying as</div>
          <div class="v">{{ isset($parent->first_name) ? trim($parent->first_name . ' ' . ($parent->last_name ?? '')) : 'Parent' }}</div>
        </div>
        <div class="item">
          <div class="k">Date</div>
          <div class="v">{{ now()->format('j M Y') }}</div>
        </div>
      </div>
    </div>
  </div>

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
            <div class="pp-item-desc">{{ $payment->description ?? 'School Fees' }}</div>
            <div class="pp-item-term">{{ $payment->term ?? '' }}</div>
          </div>
          <div>
            @if($payment->balance > 0)
              <div class="pp-amount">K{{ number_format($payment->balance, 2) }}</div>
              <div class="pp-amount-label">balance due</div>
            @else
              <span class="pp-paid-pill"><i class="fas fa-check-circle"></i> Fully paid</span>
            @endif
          </div>
        </div>

        @if($payment->balance > 0)
          <div class="pp-btn-row">
            <a href="{{ route('parent.manual.payment.form', $payment->id) }}" class="pp-btn pp-btn-secondary">
              <i class="fas fa-university"></i> Bank / Mobile Money Transfer
            </a>
            <button type="button" class="pp-btn pp-btn-primary" onclick="openMomo({{ $payment->id }}, {{ $payment->balance }})">
              <i class="fas fa-bolt"></i> Pay now (instant)
            </button>
          </div>
        @endif
      </div>
    </div>
  @empty
    <div class="pp-empty">
      <div class="big">Nothing due</div>
      There are no fee items on record for this pupil yet.
    </div>
  @endforelse

</div>

<!-- Instant mobile money modal -->
<div class="pp-modal-overlay" id="momoOverlay">
  <div class="pp-modal">
    <h3><i class="fas fa-mobile-alt"></i> Pay with mobile money</h3>
    <p class="pp-meta" style="margin-top: 2px; margin-bottom: 14px;">
      <i class="fas fa-user-graduate"></i> Paying for <strong>{{ $studentName }}</strong> ({{ $pupil->class->name ?? '—' }})
    </p>
    <form id="momoForm" method="POST">
      @csrf
      <div class="pp-field">
        <label class="pp-label" for="amount_to_pay">Amount (ZMW)</label>
        <input type="number" id="amount_to_pay" name="amount_to_pay" class="pp-input" step="0.01" min="0.01" placeholder="0.00" required>
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
  document.getElementById('amount_to_pay').value = balance;
  document.getElementById('momoOverlay').classList.add('open');
}
function closeMomo(){
  document.getElementById('momoOverlay').classList.remove('open');
}
</script>
@endsection