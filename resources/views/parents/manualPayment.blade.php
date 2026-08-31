@extends('layouts.app')

@section('content')
@include('partials.payment-theme')

<div class="pp-wrap">

  <h1 class="pp-title"><i class="fas fa-university"></i>&nbsp; Pay by transfer</h1>
  <p class="pp-sub">{{ $school->name ?? 'School' }} · Send payment using the details below, then tell us what you paid.</p>

  @if(session('error'))
    <div class="pp-alert pp-alert-danger"><i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span></div>
  @endif
  @if($errors->any())
    <div class="pp-alert pp-alert-danger"><i class="fas fa-exclamation-circle"></i><span>{{ $errors->first() }}</span></div>
  @endif

  {{-- Balance + payment details --}}
  <div class="pp-card">
    <div class="pp-card-body">

      <div class="pp-badge">
        <div class="pp-badge-icon"><i class="fas fa-wallet"></i></div>
        <div>
          <p class="pp-badge-label">Balance due</p>
          <p class="pp-badge-value">K{{ number_format($payment->balance, 2) }}</p>
        </div>
      </div>

      <div class="pp-tabs">
        <button type="button" class="pp-tab-btn active" onclick="switchTab('bank', this)">Bank Transfer</button>
        <button type="button" class="pp-tab-btn" onclick="switchTab('momo', this)">Mobile Money</button>
      </div>

      <div id="tab-bank" class="pp-tab-panel active">
        <div class="pp-detail-row">
          <span class="k">Bank</span>
          <span class="v">{{ $paymentDetail->bank_name ?? '—' }}</span>
        </div>
        <div class="pp-detail-row">
          <span class="k">Account name</span>
          <span class="v">{{ $paymentDetail->bank_account_name ?? '—' }}</span>
        </div>
        <div class="pp-detail-row">
          <span class="k">Account number</span>
          <span class="v">
            {{ $paymentDetail->bank_account_number ?? '—' }}
            @if($paymentDetail->bank_account_number)
              <button type="button" class="pp-copy-btn" onclick="copyText('{{ $paymentDetail->bank_account_number }}', this)">Copy</button>
            @endif
          </span>
        </div>
        <div class="pp-detail-row">
          <span class="k">Branch</span>
          <span class="v">{{ $paymentDetail->bank_branch ?? '—' }}</span>
        </div>
        @if($paymentDetail->bank_swift_code)
        <div class="pp-detail-row">
          <span class="k">Swift code</span>
          <span class="v">{{ $paymentDetail->bank_swift_code }}</span>
        </div>
        @endif
      </div>

      <div id="tab-momo" class="pp-tab-panel">
        <div class="pp-detail-row">
          <span class="k">Provider</span>
          <span class="v">{{ $paymentDetail->mobile_money_provider ?? '—' }}</span>
        </div>
        <div class="pp-detail-row">
          <span class="k">Number</span>
          <span class="v">
            {{ $paymentDetail->mobile_money_number ?? '—' }}
            @if($paymentDetail->mobile_money_number)
              <button type="button" class="pp-copy-btn" onclick="copyText('{{ $paymentDetail->mobile_money_number }}', this)">Copy</button>
            @endif
          </span>
        </div>
        <div class="pp-detail-row">
          <span class="k">Registered name</span>
          <span class="v">{{ $paymentDetail->mobile_money_account_name ?? '—' }}</span>
        </div>
      </div>

      @if($paymentDetail->payment_instructions)
        <div class="pp-instructions"><i class="fas fa-info-circle"></i> {{ $paymentDetail->payment_instructions }}</div>
      @endif

    </div>
  </div>

  {{-- Submission form --}}
  <div class="pp-card">
    <div class="pp-card-body">
      <h2 style="font-size:1rem; font-weight:700; color:#0f5132; margin:0 0 4px;">Confirm what you paid</h2>
      <p class="pp-hint" style="margin-top:2px;">Enter the transaction reference and/or upload a screenshot or receipt. At least one is required.</p>

      <form action="{{ route('parent.manual.payment.submit', $payment->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="pp-field">
          <label class="pp-label" for="amount_to_pay">Amount paid (K)</label>
          <input type="number" step="0.01" min="0.01" max="{{ $payment->balance }}"
                 id="amount_to_pay" name="amount_to_pay" class="pp-input"
                 value="{{ old('amount_to_pay', $payment->balance) }}" required>
        </div>

        <div class="pp-field">
          <label class="pp-label" for="parent_reference">Transaction reference</label>
          <input type="text" id="parent_reference" name="parent_reference" class="pp-input"
                 placeholder="e.g. MP240718.1345.A00123"
                 value="{{ old('parent_reference') }}">
          @error('parent_reference')<div class="pp-error">{{ $message }}</div>@enderror
        </div>

        <div class="pp-divider">and / or</div>

        <div class="pp-field">
          <label class="pp-label" for="proof_of_payment">Proof of payment</label>
          <label class="pp-dropzone" id="dropzoneLabel">
            <i class="fas fa-cloud-upload-alt"></i>
            <span id="dropzoneText">&nbsp;Tap to upload a screenshot or PDF receipt (max 5MB)</span>
            <input type="file" id="proof_of_payment" name="proof_of_payment" accept=".jpg,.jpeg,.png,.pdf" onchange="fileChosen(this)">
          </label>
        </div>

        <button type="submit" class="pp-btn pp-btn-primary">
          <i class="fas fa-check-circle"></i> Submit for verification
        </button>
      </form>
    </div>
  </div>

  <a href="{{ url()->previous() }}" class="pp-back"><i class="fas fa-arrow-left"></i> Back to payment options</a>
</div>

<script>
function switchTab(name, btn){
  document.querySelectorAll('.pp-tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.pp-tab-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('tab-' + name).classList.add('active');
}
function copyText(text, btn){
  navigator.clipboard.writeText(text).then(() => {
    const original = btn.textContent;
    btn.textContent = 'Copied';
    setTimeout(() => btn.textContent = original, 1500);
  });
}
function fileChosen(input){
  const label = document.getElementById('dropzoneLabel');
  const text = document.getElementById('dropzoneText');
  if(input.files && input.files.length){
    label.classList.add('has-file');
    text.textContent = '✓ ' + input.files[0].name;
  } else {
    label.classList.remove('has-file');
    text.textContent = 'Tap to upload a screenshot or PDF receipt (max 5MB)';
  }
}
</script>
@endsection