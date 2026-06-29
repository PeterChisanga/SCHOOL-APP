@extends('layouts.app')

@section('content')
<style>
  .content-wrapper { background: #f0f4f8 !important; }
  .ps-wrap { padding: 40px 20px; max-width: 520px; margin: 0 auto; }

  .ps-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
    padding: 40px 32px;
    text-align: center;
  }

  .ps-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem;
    margin: 0 auto 20px;
  }
  .ps-icon-pending  { background: #fffbeb; color: #d97706; }
  .ps-icon-success  { background: #ecfdf5; color: #059669; }
  .ps-icon-failed   { background: #fff5f5; color: #dc2626; }
  .ps-icon-offline  { background: #eff6ff; color: #2563eb; }

  .ps-title { font-size: 1.2rem; font-weight: 700; color: #111; margin-bottom: 8px; }
  .ps-message { font-size: .88rem; color: #6b7280; margin-bottom: 24px; line-height: 1.6; }

  .ps-ref {
    background: #f9fafb; border: 1px solid #e5e7eb;
    border-radius: 8px; padding: 10px 16px;
    font-size: .78rem; color: #9ca3af;
    margin-bottom: 28px;
  }
  .ps-ref span { font-weight: 600; color: #374151; }

  .ps-spinner {
    width: 40px; height: 40px;
    border: 3px solid #e5e7eb;
    border-top-color: #0f5132;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 20px;
  }
  @keyframes spin { to { transform: rotate(360deg); } }

  .ps-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 11px 24px; border-radius: 8px;
    font-size: .88rem; font-weight: 600;
    border: none; cursor: pointer;
    font-family: inherit; text-decoration: none;
    margin: 4px;
  }
  .ps-btn-primary   { background: #0f5132; color: #fff; }
  .ps-btn-secondary { background: #e5e7eb; color: #374151; }
</style>

<div class="ps-wrap">
  <div class="ps-card">

    {{-- Pending state (shown by default, JS updates it) --}}
    <div id="ps-pending">
      <div class="ps-spinner"></div>
      <p class="ps-title">Processing Payment</p>
      <p class="ps-message">Please wait while we confirm your payment.<br>This usually takes a few seconds.</p>
    </div>

    {{-- Success state (hidden until JS reveals it) --}}
    <div id="ps-success" style="display:none;">
      <div class="ps-icon ps-icon-success"><i class="fas fa-check-circle"></i></div>
      <p class="ps-title">Payment Successful!</p>
      <p class="ps-message">Your payment has been confirmed and your balance has been updated.</p>
      <a href="{{ route('parent.payments', session('otp_pupil_id')) }}" class="ps-btn ps-btn-primary">
        <i class="fas fa-eye"></i> View Payments
      </a>
    </div>

    {{-- Failed state --}}
    <div id="ps-failed" style="display:none;">
      <div class="ps-icon ps-icon-failed"><i class="fas fa-times-circle"></i></div>
      <p class="ps-title">Payment Failed</p>
      <p class="ps-message" id="ps-fail-message">The payment could not be completed. Please try again.</p>
      <a href="{{ route('parent.payments', session('otp_pupil_id')) }}" class="ps-btn ps-btn-primary">
        <i class="fas fa-redo"></i> Try Again
      </a>
    </div>

    {{-- Pay offline state --}}
    <div id="ps-offline" style="display:none;">
      <div class="ps-icon ps-icon-offline"><i class="fas fa-mobile-alt"></i></div>
      <p class="ps-title">Check Your Phone</p>
      <p class="ps-message">A payment prompt has been sent to your phone. Please open your mobile money app and authorise the payment.</p>
    </div>

    {{-- Reference --}}
    <div class="ps-ref">
      Reference: <span>{{ $reference }}</span>
    </div>

    <a href="{{ route('parent.search.page') }}" class="ps-btn ps-btn-secondary">
      <i class="fas fa-arrow-left"></i> Back to Search
    </a>

  </div>
</div>

<script>
  const pollUrl   = "{{ route('parent.payment.poll') }}";
  let   attempts  = 0;
  const maxAttempts = 40; // ~2 minutes at 3s intervals

  function showState(state, message) {
    ['pending','success','failed','offline'].forEach(s => {
      document.getElementById('ps-' + s).style.display = 'none';
    });
    document.getElementById('ps-' + state).style.display = 'block';
    if (message && state === 'failed') {
      document.getElementById('ps-fail-message').textContent = message;
    }
  }

  function poll() {
    if (attempts >= maxAttempts) {
      showState('failed', 'Payment timed out. Please check your balance and try again if needed.');
      return;
    }

    attempts++;

    fetch(pollUrl, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      switch (data.status) {
        case 'successful':
          showState('success');
          break;
        case 'failed':
          showState('failed', data.message);
          break;
        case 'pay-offline':
          showState('offline');
          setTimeout(poll, 3000); // keep polling — waiting for user to approve on phone
          break;
        case 'otp-required':
          showState('pending');
          setTimeout(poll, 3000);
          break;
        case 'pending':
        default:
          setTimeout(poll, 3000);
          break;
      }
    })
    .catch(() => setTimeout(poll, 5000)); // on network error, retry after 5s
  }

  // Start polling immediately
  poll();
</script>
@endsection