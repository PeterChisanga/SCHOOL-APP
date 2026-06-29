@extends('layouts.app')

@section('content')
<style>
  .content-wrapper { background: #f0f4f8 !important; }

  .otp-wrap { padding: 30px 20px 60px; max-width: 500px; margin: 0 auto; }

  .otp-header {
    background: linear-gradient(135deg, #0f5132, #1e8449);
    border-radius: 16px;
    padding: 28px 32px;
    color: #fff;
    margin-bottom: 24px;
    position: relative; overflow: hidden;
  }
  .otp-header::after {
    content: '';
    position: absolute; right: -40px; top: -40px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
  }
  .otp-header h1 { font-size: 1.4rem; font-weight: 700; margin: 0 0 4px; }
  .otp-header p  { font-size: .86rem; opacity: .8; margin: 0; }

  .otp-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    border: 1px solid #e5e7eb;
    overflow: hidden;
  }

  .otp-card-body { padding: 32px; }

  .otp-phone-badge {
    display: flex; align-items: center; gap: 12px;
    background: #f0faf4; border: 1px solid #a8e0bc;
    border-radius: 10px; padding: 14px 16px;
    margin-bottom: 28px;
  }
  .otp-phone-icon {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, #0f5132, #1e8449);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .9rem; flex-shrink: 0;
  }
  .otp-phone-label { font-size: .75rem; color: #6b7280; margin: 0 0 2px; }
  .otp-phone-number { font-size: .95rem; font-weight: 700; color: #0f5132; margin: 0; }

  .otp-alert {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 13px 16px; border-radius: 10px;
    font-size: .86rem; margin-bottom: 20px;
  }
  .otp-alert i { margin-top: 1px; flex-shrink: 0; }
  .otp-alert-danger  { background: #fff5f5; border: 1px solid #fcc; color: #c0392b; }
  .otp-alert-success { background: #f0faf4; border: 1px solid #a8e0bc; color: #145a32; }

  .otp-label {
    display: block; font-size: .82rem; font-weight: 600;
    color: #374151; margin-bottom: 10px; text-align: center;
  }

  .otp-input-wrap {
    display: flex; justify-content: center; margin-bottom: 8px;
  }
  .otp-input {
    width: 100%; max-width: 280px;
    padding: 16px 12px;
    border: 2px solid #d1d5db; border-radius: 12px;
    font-size: 2rem; font-weight: 700;
    text-align: center; letter-spacing: .4em;
    color: #0f5132; outline: none;
    transition: border-color .2s, box-shadow .2s;
    font-family: monospace;
  }
  .otp-input:focus {
    border-color: #0f5132;
    box-shadow: 0 0 0 4px rgba(15,81,50,.10);
  }
  .otp-input.is-error { border-color: #dc2626; }
  .otp-hint { text-align: center; font-size: .75rem; color: #9ca3af; margin-bottom: 24px; }
  .otp-error { text-align: center; font-size: .78rem; color: #dc2626; margin-bottom: 16px; }

  .otp-btn-submit {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 13px;
    background: #0f5132; color: #fff;
    border: none; border-radius: 10px;
    font-size: .92rem; font-weight: 600;
    cursor: pointer; font-family: inherit;
    box-shadow: 0 3px 10px rgba(15,81,50,.25);
    transition: background .2s, transform .15s;
  }
  .otp-btn-submit:hover { background: #1a7a4a; transform: translateY(-1px); }

  .otp-divider {
    display: flex; align-items: center; gap: 12px;
    margin: 20px 0; color: #d1d5db; font-size: .8rem;
  }
  .otp-divider::before, .otp-divider::after {
    content: ''; flex: 1; height: 1px; background: #e5e7eb;
  }

  .otp-btn-resend {
    display: flex; align-items: center; justify-content: center; gap: 7px;
    width: 100%; padding: 11px;
    background: #f9fafb; color: #374151;
    border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: .85rem; font-weight: 600;
    cursor: pointer; font-family: inherit;
    transition: background .2s;
  }
  .otp-btn-resend:hover { background: #f0faf4; border-color: #a8e0bc; color: #0f5132; }

  .otp-back {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    margin-top: 20px;
    font-size: .82rem; color: #6b7280; text-decoration: none;
  }
  .otp-back:hover { color: #0f5132; }

  .otp-expires {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    font-size: .78rem; color: #9ca3af; margin-top: 12px;
  }
  #otp-timer { font-weight: 600; color: #0f5132; }
  #otp-timer.expiring { color: #dc2626; }
</style>

<div class="otp-wrap">

  {{-- Header --}}
  <div class="otp-header">
    <h1><i class="fas fa-shield-alt"></i> &nbsp;Phone Verification</h1>
    <p>Confirm your identity to access fee records</p>
  </div>

  {{-- Card --}}
  <div class="otp-card">
    <div class="otp-card-body">

      {{-- Phone badge --}}
      <div class="otp-phone-badge">
        <div class="otp-phone-icon"><i class="fas fa-mobile-alt"></i></div>
        <div>
          <p class="otp-phone-label">Code sent to</p>
          <p class="otp-phone-number">{{ session('otp_phone') }}</p>
        </div>
      </div>

      {{-- Alerts --}}
      @if(session('error'))
        <div class="otp-alert otp-alert-danger">
          <i class="fas fa-exclamation-circle"></i>
          <span>{{ session('error') }}</span>
        </div>
      @endif
      @if(session('success'))
        <div class="otp-alert otp-alert-success">
          <i class="fas fa-check-circle"></i>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      {{-- OTP form --}}
      <form method="POST" action="{{ route('parent.otp.verify') }}">
        @csrf

        <label class="otp-label">Enter your 6-digit verification code</label>

        <div class="otp-input-wrap">
          <input
            type="text"
            name="otp"
            maxlength="6"
            inputmode="numeric"
            placeholder="——————"
            autofocus
            class="otp-input @error('otp') is-error @enderror"
          />
        </div>

        @error('otp')
          <p class="otp-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
        @enderror

        <div class="otp-expires">
          <i class="fas fa-clock"></i>
          Code expires in <span id="otp-timer">10:00</span>
        </div>

        <div style="margin-top: 20px;">
          <button type="submit" class="otp-btn-submit">
            <i class="fas fa-check-circle"></i> Verify & Continue
          </button>
        </div>
      </form>

      <div class="otp-divider">or</div>

      {{-- Resend form --}}
      <form method="POST" action="{{ route('parent.otp.resend') }}">
        @csrf
        <button type="submit" class="otp-btn-resend">
          <i class="fas fa-redo"></i> Resend Code
        </button>
      </form>

      <a href="{{ route('parent.search.page') }}" class="otp-back">
        <i class="fas fa-arrow-left"></i> Back to Search
      </a>

    </div>
  </div>

</div>

<script>
  // Countdown timer — 10 minutes
  let seconds = 600;
  const timerEl = document.getElementById('otp-timer');

  const tick = setInterval(() => {
    seconds--;
    if (seconds <= 0) {
      clearInterval(tick);
      timerEl.textContent = 'Expired';
      timerEl.classList.add('expiring');
      return;
    }
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    timerEl.textContent = `${m}:${s.toString().padStart(2, '0')}`;
    if (seconds <= 60) timerEl.classList.add('expiring');
  }, 1000);

  // Auto-submit when 6 digits entered
  document.querySelector('.otp-input').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
    if (this.value.length === 6) this.closest('form').submit();
  });
</script>

@endsection