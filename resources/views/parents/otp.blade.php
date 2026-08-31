@extends('layouts.app')

@section('content')
@include('partials.payment-theme')

<div class="pp-wrap">

  <h1 class="pp-title"><i class="fas fa-shield-alt"></i>&nbsp; Phone verification</h1>
  <p class="pp-sub">Confirm your identity to access fee records.</p>

  <div class="pp-card">
    <div class="pp-card-body">

      {{-- Phone badge --}}
      <div class="pp-badge">
        <div class="pp-badge-icon"><i class="fas fa-mobile-alt"></i></div>
        <div>
          <p class="pp-badge-label">Code sent to</p>
          <p class="pp-badge-value">{{ session('otp_phone') }}</p>
        </div>
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

      {{-- OTP form --}}
      <form method="POST" action="{{ route('parent.otp.verify') }}">
        @csrf

        <label class="pp-label" style="text-align:center;">Enter your 6-digit verification code</label>

        <div style="display:flex; justify-content:center; margin-bottom:8px;">
          <input
            type="text"
            name="otp"
            maxlength="6"
            inputmode="numeric"
            placeholder="——————"
            autofocus
            class="pp-input @error('otp') is-error @enderror"
            style="max-width:280px; font-size:2rem; font-weight:700; text-align:center; letter-spacing:.4em; color:#0f5132; font-family:monospace;"
          />
        </div>

        @error('otp')
          <p class="pp-error" style="text-align:center;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
        @enderror

        <div style="display:flex; align-items:center; justify-content:center; gap:6px; font-size:.78rem; color:#9ca3af; margin:12px 0 20px;">
          <i class="fas fa-clock"></i>
          Code expires in <span id="otp-timer" style="font-weight:600; color:#0f5132;">10:00</span>
        </div>

        <button type="submit" class="pp-btn pp-btn-primary">
          <i class="fas fa-check-circle"></i> Verify &amp; continue
        </button>
      </form>

      <div class="pp-divider">or</div>

      {{-- Resend form --}}
      <form method="POST" action="{{ route('parent.otp.resend') }}">
        @csrf
        <button type="submit" class="pp-btn pp-btn-ghost">
          <i class="fas fa-redo"></i> Resend code
        </button>
      </form>

      <a href="{{ route('parent.search.page') }}" class="pp-back">
        <i class="fas fa-arrow-left"></i> Back to search
      </a>

    </div>
  </div>

</div>

<script>
  let seconds = 600;
  const timerEl = document.getElementById('otp-timer');

  const tick = setInterval(() => {
    seconds--;
    if (seconds <= 0) {
      clearInterval(tick);
      timerEl.textContent = 'Expired';
      timerEl.style.color = '#dc2626';
      return;
    }
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    timerEl.textContent = `${m}:${s.toString().padStart(2, '0')}`;
    if (seconds <= 60) timerEl.style.color = '#dc2626';
  }, 1000);

  document.querySelector('input[name="otp"]').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
    if (this.value.length === 6) this.closest('form').submit();
  });
</script>
@endsection