@extends('layouts.app')

@section('content')
<style>
  .content-wrapper { background: #f0f4f8 !important; }

  .pst-wrap {
    min-height: 85vh;
    display: flex; align-items: center; justify-content: center;
    padding: 40px 16px;
  }

  .pst-card {
    width: 100%; max-width: 520px;
    background: #fff; border-radius: 20px;
    box-shadow: 0 8px 40px rgba(0,0,0,.12);
    overflow: hidden;
  }

  /* header */
  .pst-head {
    background: linear-gradient(135deg, #0f5132, #1e8449);
    padding: 32px 36px 28px; color: #fff;
    position: relative; overflow: hidden;
  }
  .pst-head::after {
    content: ''; position: absolute;
    right: -40px; top: -40px;
    width: 180px; height: 180px; border-radius: 50%;
    background: rgba(255,255,255,.06);
  }
  .pst-head h2 { font-size: 1.35rem; font-weight: 700; margin: 0 0 5px; position: relative; z-index:1; }
  .pst-head p  { font-size: .85rem; opacity: .8; margin: 0; position: relative; z-index:1; }

  .pst-body { padding: 32px 36px; }

  /* reference table */
  .pst-ref-table { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
  .pst-ref-table tr td {
    padding: 10px 14px; font-size: .86rem;
    border-bottom: 1px solid #f0f0f0;
  }
  .pst-ref-table tr td:first-child { color: #6b7280; font-weight: 600; width: 40%; }
  .pst-ref-table tr td:last-child  { color: #111; font-weight: 500; }
  .pst-ref-table tr:last-child td  { border-bottom: none; }

  /* status panels */
  .pst-panel { display: none; }
  .pst-panel.active { display: block; }

  .pst-status-box {
    border-radius: 12px; padding: 20px 22px;
    display: flex; align-items: flex-start; gap: 14px;
    margin-bottom: 22px;
  }
  .pst-status-box .pst-status-icon {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
  }
  .pst-status-box .pst-status-text strong { display: block; font-size: .96rem; margin-bottom: 4px; }
  .pst-status-box .pst-status-text p { font-size: .83rem; margin: 0; line-height: 1.5; }

  /* pending */
  .pst-pending-box { background: #eff6ff; border: 1px solid #bfdbfe; }
  .pst-pending-box .pst-status-icon { background: #dbeafe; color: #1d4ed8; }
  .pst-pending-box .pst-status-text strong { color: #1e3a8a; }
  .pst-pending-box .pst-status-text p { color: #1e40af; }

  /* spinner */
  .pst-spinner {
    width: 20px; height: 20px;
    border: 2px solid #bfdbfe;
    border-top-color: #1d4ed8;
    border-radius: 50%;
    animation: spin .7s linear infinite;
    display: inline-block; margin-right: 6px;
    vertical-align: middle;
  }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* success */
  .pst-success-box { background: #f0fdf4; border: 1px solid #a7f3d0; }
  .pst-success-box .pst-status-icon { background: #dcfce7; color: #16a34a; }
  .pst-success-box .pst-status-text strong { color: #14532d; }
  .pst-success-box .pst-status-text p { color: #166534; }

  /* failed */
  .pst-failed-box { background: #fff5f5; border: 1px solid #fca5a5; }
  .pst-failed-box .pst-status-icon { background: #fee2e2; color: #dc2626; }
  .pst-failed-box .pst-status-text strong { color: #7f1d1d; }
  .pst-failed-box .pst-status-text p { color: #991b1b; }

  /* timeout */
  .pst-timeout-box { background: #fffbeb; border: 1px solid #fde68a; }
  .pst-timeout-box .pst-status-icon { background: #fef3c7; color: #d97706; }
  .pst-timeout-box .pst-status-text strong { color: #78350f; }
  .pst-timeout-box .pst-status-text p { color: #92400e; }

  /* poll counter */
  .pst-poll-info {
    font-size: .77rem; color: #9ca3af;
    text-align: center; margin-bottom: 16px;
  }

  /* actions */
  .pst-actions { display: flex; gap: 10px; flex-wrap: wrap; }
  .pst-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 20px; border-radius: 8px;
    font-size: .85rem; font-weight: 600;
    border: none; cursor: pointer;
    transition: background .2s, transform .15s;
    text-decoration: none; font-family: inherit;
  }
  .pst-btn-primary   { background: #0f5132; color: #fff; box-shadow: 0 3px 10px rgba(15,81,50,.25); }
  .pst-btn-primary:hover { background: #1a7a4a; transform: translateY(-1px); color: #fff; }
  .pst-btn-secondary { background: #e5e7eb; color: #374151; }
  .pst-btn-secondary:hover { background: #d1d5db; }
  .pst-btn-danger    { background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5; }
  .pst-btn-danger:hover { background: #fee2e2; }
</style>

<div class="pst-wrap">
  <div class="pst-card">

    <div class="pst-head">
      <h2><i class="fas fa-satellite-dish"></i> &nbsp;Payment Status</h2>
      <p>We are confirming your transaction with Lipila</p>
    </div>

    <div class="pst-body">

      {{-- Reference details --}}
      @if($payment)
      <table class="pst-ref-table">
        <tr>
          <td>Payment Type</td>
          <td>{{ $payment->type }}</td>
        </tr>
        <tr>
          <td>Term</td>
          <td>{{ $payment->term ?? '—' }}</td>
        </tr>
        <tr>
          <td>Reference</td>
          <td><code style="background:#f3f4f6;padding:3px 8px;border-radius:5px;font-size:.82rem;">{{ $referenceId }}</code></td>
        </tr>
      </table>
      @endif

      {{-- PENDING panel --}}
      <div class="pst-panel active" id="panel-pending">
        <div class="pst-status-box pst-pending-box">
          <div class="pst-status-icon"><i class="fas fa-hourglass-half"></i></div>
          <div class="pst-status-text">
            <strong>Awaiting Confirmation</strong>
            <p>Please approve the payment prompt on your phone. Keep this page open.</p>
          </div>
        </div>
        <p class="pst-poll-info">
          <span class="pst-spinner"></span>
          <span id="poll-text">Checking status…</span>
        </p>
        <div class="pst-actions">
          <button class="pst-btn pst-btn-danger" onclick="stopPolling()">
            <i class="fas fa-stop-circle"></i> Stop Checking
          </button>
        </div>
      </div>

      {{-- SUCCESS panel --}}
      <div class="pst-panel" id="panel-success">
        <div class="pst-status-box pst-success-box">
          <div class="pst-status-icon"><i class="fas fa-check-circle"></i></div>
          <div class="pst-status-text">
            <strong>Payment Successful!</strong>
            <p>Your payment has been confirmed. Your balance has been updated.</p>
          </div>
        </div>
        <div class="pst-actions">
          @if($payment)
          <a href="{{ route('parent.payments', $payment->pupil_id) }}" class="pst-btn pst-btn-primary">
            <i class="fas fa-eye"></i> View Updated Balance
          </a>
          @endif
          <a href="{{ route('parent.search.page') }}" class="pst-btn pst-btn-secondary">
            <i class="fas fa-home"></i> Back to Search
          </a>
        </div>
      </div>

      {{-- FAILED panel --}}
      <div class="pst-panel" id="panel-failed">
        <div class="pst-status-box pst-failed-box">
          <div class="pst-status-icon"><i class="fas fa-times-circle"></i></div>
          <div class="pst-status-text">
            <strong>Payment Failed</strong>
            <p id="failed-message">The transaction could not be completed. Please check your mobile money balance and try again.</p>
          </div>
        </div>
        <div class="pst-actions">
          @if($payment)
          <a href="{{ route('parent.payments', $payment->pupil_id) }}" class="pst-btn pst-btn-primary">
            <i class="fas fa-redo"></i> Try Again
          </a>
          @endif
          <a href="{{ route('parent.search.page') }}" class="pst-btn pst-btn-secondary">
            <i class="fas fa-home"></i> Back to Search
          </a>
        </div>
      </div>

      {{-- TIMEOUT panel --}}
      <div class="pst-panel" id="panel-timeout">
        <div class="pst-status-box pst-timeout-box">
          <div class="pst-status-icon"><i class="fas fa-clock"></i></div>
          <div class="pst-status-text">
            <strong>Confirmation Timed Out</strong>
            <p>We could not confirm your payment in time. If you approved the prompt, your balance will update automatically once Lipila confirms. Otherwise, please try again.</p>
          </div>
        </div>
        <div class="pst-actions">
          @if($payment)
          <a href="{{ route('parent.payments', $payment->pupil_id) }}" class="pst-btn pst-btn-primary">
            <i class="fas fa-arrow-left"></i> Return to Payments
          </a>
          @endif
          <a href="{{ route('parent.search.page') }}" class="pst-btn pst-btn-secondary">
            <i class="fas fa-home"></i> Back to Search
          </a>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  const PAYMENT_ID   = "{{ $referenceId }}";
  const CHECK_URL    = "{{ route('parent.payment.check-status') }}";
  const CSRF         = "{{ csrf_token() }}";
  const MAX_ATTEMPTS = 20;        // 20 × 6s = 2 minutes
  const INTERVAL_MS  = 6000;

  let attempts = 0;
  let polling  = null;

  function showPanel(name) {
    document.querySelectorAll('.pst-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('active');
  }

  async function checkStatus() {
    attempts++;
    const pollText = document.getElementById('poll-text');
    if (pollText) pollText.textContent = 'Attempt ' + attempts + ' of ' + MAX_ATTEMPTS + '…';

    if (attempts > MAX_ATTEMPTS) {
      stopPolling();
      showPanel('timeout');
      return;
    }

    try {
      const res = await fetch(CHECK_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ payment_id: PAYMENT_ID })
      });

      const json = await res.json();
      if (!json.success) return;

      // Lipila returns "Successful" or "Failed" (capital first letter)
      const status = (json.data?.status ?? '').toLowerCase();

      if (status === 'successful') {
        stopPolling();
        showPanel('success');
      } else if (status === 'failed') {
        stopPolling();
        // Show the failure reason from Lipila if available
        const msg = json.data?.message ?? null;
        if (msg) {
          const el = document.getElementById('failed-message');
          if (el) el.textContent = msg;
        }
        showPanel('failed');
      }
      // status === 'pending' → keep polling

    } catch (e) {
      // keep polling on network errors
    }
  }

  function stopPolling() {
    if (polling) { clearInterval(polling); polling = null; }
  }

  polling = setInterval(checkStatus, INTERVAL_MS);
  checkStatus(); // run immediately on page load
</script>
@endsection