@extends('layouts.app')

@section('content')
<style>
  .content-wrapper { background: #f0f4f8 !important; }

  .pp-wrap { padding: 30px 20px 60px; max-width: 860px; margin: 0 auto; }

  /* ── page title ── */
  .pp-header {
    background: linear-gradient(135deg, #0f5132, #1e8449);
    border-radius: 16px;
    padding: 28px 32px;
    color: #fff;
    margin-bottom: 24px;
    position: relative; overflow: hidden;
  }
  .pp-header::after {
    content: '';
    position: absolute; right: -40px; top: -40px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
  }
  .pp-header h1 { font-size: 1.4rem; font-weight: 700; margin: 0 0 4px; }
  .pp-header p  { font-size: .86rem; opacity: .8; margin: 0; }

  /* ── student info bar ── */
  .pp-student {
    background: #fff;
    border-radius: 14px;
    padding: 20px 24px;
    display: flex; align-items: center; gap: 16px;
    margin-bottom: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    border-left: 4px solid #0f5132;
  }
  .pp-avatar {
    width: 50px; height: 50px;
    background: linear-gradient(135deg, #0f5132, #1e8449);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.2rem; flex-shrink: 0;
  }
  .pp-student-name { font-size: 1.05rem; font-weight: 700; color: #111; margin: 0 0 3px; }
  .pp-student-sub  { font-size: .8rem; color: #6b7280; margin: 0; }
  .pp-student-badge {
    margin-left: auto;
    background: #ecfdf5; color: #065f46;
    border: 1px solid #a7f3d0;
    border-radius: 100px;
    padding: 5px 14px;
    font-size: .75rem; font-weight: 600;
  }

  /* ── alerts ── */
  .pp-alert {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 13px 16px; border-radius: 10px;
    font-size: .86rem; margin-bottom: 20px;
  }
  .pp-alert i { margin-top: 1px; flex-shrink: 0; }
  .pp-alert-danger  { background: #fff5f5; border: 1px solid #fcc; color: #c0392b; }
  .pp-alert-success { background: #f0faf4; border: 1px solid #a8e0bc; color: #145a32; }
  .pp-alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }

  /* ── empty state ── */
  .pp-empty {
    background: #fff; border-radius: 14px;
    padding: 50px 24px; text-align: center;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
  }
  .pp-empty i { font-size: 2.5rem; color: #d1d5db; margin-bottom: 14px; display: block; }
  .pp-empty h3 { font-size: 1rem; color: #374151; margin-bottom: 6px; }
  .pp-empty p  { font-size: .85rem; color: #9ca3af; }

  /* ── payment card ── */
  .pp-payment-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    margin-bottom: 18px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
  }
  .pp-payment-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 24px;
    background: #fafafa;
    border-bottom: 1px solid #f0f0f0;
  }
  .pp-payment-head-left { display: flex; align-items: center; gap: 12px; }
  .pp-type-icon {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, #0f5132, #1e8449);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .9rem; flex-shrink: 0;
  }
  .pp-type-name { font-weight: 700; font-size: .95rem; color: #111; margin: 0 0 2px; }
  .pp-type-term { font-size: .78rem; color: #6b7280; margin: 0; }

  .pp-status {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 13px; border-radius: 100px;
    font-size: .74rem; font-weight: 600;
  }
  .pp-status-cleared { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
  .pp-status-partial  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
  .pp-status-unpaid   { background: #fff5f5; color: #991b1b; border: 1px solid #fca5a5; }

  /* amounts */
  .pp-amounts {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 12px; padding: 20px 24px 0;
  }
  .pp-amount-box {
    background: #f9fafb; border: 1px solid #e5e7eb;
    border-radius: 10px; padding: 14px; text-align: center;
  }
  .pp-amount-label { font-size: .72rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 5px; }
  .pp-amount-value { font-size: 1.05rem; font-weight: 700; color: #111; }
  .pp-amount-box.is-balance .pp-amount-value { color: #dc2626; }
  .pp-amount-box.is-paid    .pp-amount-value { color: #059669; }

  /* progress */
  .pp-progress-wrap { padding: 16px 24px 0; }
  .pp-progress-labels {
    display: flex; justify-content: space-between;
    font-size: .74rem; color: #9ca3af; margin-bottom: 6px;
  }
  .pp-progress-bar {
    height: 7px; background: #e5e7eb;
    border-radius: 100px; overflow: hidden;
  }
  .pp-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0f5132, #22c55e);
    border-radius: 100px;
  }

  /* pay form */
  .pp-pay-form-wrap { padding: 0 24px 20px; margin-top: 16px; display: none; }
  .pp-pay-form-wrap.open { display: block; }
  .pp-pay-form-inner {
    background: #f0faf4; border: 1px solid #a8e0bc;
    border-radius: 12px; padding: 20px;
  }
  .pp-pay-form-inner h5 {
    font-size: .9rem; font-weight: 700; color: #0f5132;
    margin: 0 0 16px; display: flex; align-items: center; gap: 7px;
  }
  .pp-pay-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
  .pp-pay-field label {
    display: block; font-size: .78rem; font-weight: 600;
    color: #374151; margin-bottom: 5px;
  }
  .pp-pay-field input {
    width: 100%; padding: 10px 12px;
    border: 1.5px solid #d1d5db; border-radius: 8px;
    font-size: .88rem; color: #111; outline: none;
    transition: border-color .2s, box-shadow .2s;
    font-family: inherit;
  }
  .pp-pay-field input:focus {
    border-color: #0f5132;
    box-shadow: 0 0 0 3px rgba(15,81,50,.10);
  }
  .pp-pay-field .pp-field-hint { font-size: .73rem; color: #6b7280; margin-top: 4px; }
  .pp-pay-actions { display: flex; gap: 10px; margin-top: 4px; }

  .pp-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 20px; border-radius: 8px;
    font-size: .85rem; font-weight: 600;
    border: none; cursor: pointer;
    transition: background .2s, transform .15s;
    font-family: inherit;
    text-decoration: none;
  }
  .pp-btn-primary {
    background: #0f5132; color: #fff;
    box-shadow: 0 3px 10px rgba(15,81,50,.25);
  }
  .pp-btn-primary:hover  { background: #1a7a4a; transform: translateY(-1px); color: #fff; }
  .pp-btn-secondary { background: #e5e7eb; color: #374151; }
  .pp-btn-secondary:hover { background: #d1d5db; }
  .pp-btn-pay-toggle {
    background: #0f5132; color: #fff;
    padding: 8px 18px; font-size: .82rem;
    box-shadow: 0 3px 10px rgba(15,81,50,.2);
  }
  .pp-btn-pay-toggle:hover { background: #1a7a4a; color: #fff; }
  .pp-btn-sm { padding: 7px 14px; font-size: .8rem; }

  /* footer row */
  .pp-footer-row { margin-top: 28px; }
</style>

<div class="pp-wrap">

  {{-- Header --}}
  <div class="pp-header">
    <h1><i class="fas fa-file-invoice-dollar"></i> &nbsp;Fee Payments</h1>
    <p>Viewing payment records for {{ $pupil->first_name }} {{ $pupil->last_name }}</p>
  </div>

  {{-- Student info --}}
  <div class="pp-student">
    <div class="pp-avatar"><i class="fas fa-user-graduate"></i></div>
    <div>
      <p class="pp-student-name">{{ $pupil->first_name }} {{ $pupil->last_name }}</p>
      <p class="pp-student-sub">
        {{ $pupil->class->name ?? 'No Class' }}
        @if($parent) &nbsp;·&nbsp; {{ $parent->phone }} @endif
      </p>
    </div>
    <span class="pp-student-badge"><i class="fas fa-check-circle"></i> &nbsp;Verified</span>
  </div>

  {{-- Alerts --}}
  @if(session('error'))
    <div class="pp-alert pp-alert-danger">
      <i class="fas fa-exclamation-circle"></i>
      <span>{{ session('error') }}</span>
    </div>
  @endif
  @if(session('success'))
    <div class="pp-alert pp-alert-success">
      <i class="fas fa-check-circle"></i>
      <span>{{ session('success') }}</span>
    </div>
  @endif
  @if($errors->any())
    <div class="pp-alert pp-alert-danger">
      <i class="fas fa-exclamation-circle"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  {{-- Empty state --}}
  @if($payments->isEmpty())
    <div class="pp-empty">
      <i class="fas fa-folder-open"></i>
      <h3>No fee records found</h3>
      <p>There are no payment records for this pupil yet.</p>
    </div>
  @else
    {{-- Payment cards --}}
    @foreach($payments as $payment)
      @php
        $pct     = $payment->amount > 0 ? min(100, ($payment->amount_paid / $payment->amount) * 100) : 0;
        $cleared = $pct >= 100;
      @endphp

      <div class="pp-payment-card">

        {{-- Card header --}}
        <div class="pp-payment-head">
          <div class="pp-payment-head-left">
            <div class="pp-type-icon"><i class="fas fa-receipt"></i></div>
            <div>
              <p class="pp-type-name">{{ $payment->type }}</p>
              <p class="pp-type-term">{{ $payment->term ?? 'General' }}</p>
            </div>
          </div>
          @if($cleared)
            <span class="pp-status pp-status-cleared"><i class="fas fa-check-circle"></i> Cleared</span>
          @elseif($pct > 0)
            <span class="pp-status pp-status-partial"><i class="fas fa-clock"></i> Partial</span>
          @else
            <span class="pp-status pp-status-unpaid"><i class="fas fa-times-circle"></i> Unpaid</span>
          @endif
        </div>

        {{-- Amounts --}}
        <div class="pp-amounts">
          <div class="pp-amount-box">
            <div class="pp-amount-label">Total Fee</div>
            <div class="pp-amount-value">K{{ number_format($payment->amount, 2) }}</div>
          </div>
          <div class="pp-amount-box is-paid">
            <div class="pp-amount-label">Amount Paid</div>
            <div class="pp-amount-value">K{{ number_format($payment->amount_paid, 2) }}</div>
          </div>
          <div class="pp-amount-box is-balance">
            <div class="pp-amount-label">Balance</div>
            <div class="pp-amount-value">K{{ number_format($payment->balance, 2) }}</div>
          </div>
        </div>

        {{-- Progress bar --}}
        <div class="pp-progress-wrap">
          <div class="pp-progress-labels">
            <span>Payment Progress</span>
            <span>{{ number_format($pct, 0) }}% paid</span>
          </div>
          <div class="pp-progress-bar">
            <div class="pp-progress-fill" style="width: {{ $pct }}%"></div>
          </div>
        </div>

        {{-- Pay form toggle --}}
        @if(!$cleared)
          <div style="padding: 16px 24px 20px; display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:.8rem; color:#6b7280;">Balance due: <strong style="color:#dc2626;">K{{ number_format($payment->balance, 2) }}</strong></span>
            <button class="pp-btn pp-btn-pay-toggle" onclick="togglePayForm('form-{{ $payment->id }}', this)">
              <i class="fas fa-credit-card"></i> Pay Now
            </button>
          </div>

          {{-- Inline pay form --}}
          <div class="pp-pay-form-wrap" id="form-{{ $payment->id }}">
            <div class="pp-pay-form-inner">
              <h5><i class="fas fa-mobile-alt"></i> Pay via Mobile Money — {{ $payment->type }}</h5>
              <form method="POST" action="{{ route('parent.pay', $payment->id) }}">
                @csrf
                <div class="pp-pay-row">
                  <div class="pp-pay-field">
                    <label>Amount to Pay (K)</label>
                    <input
                      type="number"
                      name="amount_to_pay"
                      step="0.01" min="0.01"
                      max="{{ $payment->balance }}"
                      placeholder="0.00"
                      required
                    />
                    <p class="pp-field-hint">Max: K{{ number_format($payment->balance, 2) }}</p>
                  </div>
                  <div class="pp-pay-field">
                    <label>Mobile Money Number</label>
                    <input
                      type="tel"
                      name="payment_phone"
                      placeholder="e.g. 0971234567"
                      value="{{ $parent->phone ?? '' }}"
                      required
                    />
                    <p class="pp-field-hint">Airtel or MTN Money number</p>
                  </div>
                </div>
                <div class="pp-pay-actions">
                  <button type="submit" class="pp-btn pp-btn-primary">
                    <i class="fas fa-lock"></i> Pay via Tumeny
                  </button>
                  <button type="button" class="pp-btn pp-btn-secondary" onclick="togglePayForm('form-{{ $payment->id }}', null)">
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          </div>
        @else
          <div style="padding: 16px 24px 20px;">
            <span style="color:#059669; font-size:.85rem; font-weight:600;"><i class="fas fa-check-circle"></i> Fully paid — no balance outstanding</span>
          </div>
        @endif

      </div>
    @endforeach
  @endif

  {{-- Footer --}}
  <div class="pp-footer-row">
    <a href="{{ route('parent.search.page') }}" class="pp-btn pp-btn-secondary">
      <i class="fas fa-arrow-left"></i> Back to Search
    </a>
  </div>

</div>

<script>
  function togglePayForm(id, btn) {
    const el = document.getElementById(id);
    const isOpen = el.classList.contains('open');
    // close all
    document.querySelectorAll('.pp-pay-form-wrap').forEach(f => f.classList.remove('open'));
    if (!isOpen) el.classList.add('open');
  }
</script>
@endsection