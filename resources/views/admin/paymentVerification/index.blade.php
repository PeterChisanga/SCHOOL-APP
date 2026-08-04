@extends('layouts.app')

@section('content')
@include('partials.payment-theme')

<div class="pp-wrap pp-wide">

  <div class="pp-toolbar">
    <div>
      <h1 class="pp-title"><i class="fas fa-tasks"></i>&nbsp; Verification queue</h1>
      <p class="pp-sub" style="margin-bottom:0;">Manual payments waiting for review.</p>
    </div>
    <span class="pp-count-pill">{{ $pendingTransactions->count() }} pending</span>
  </div>

  <form method="GET" style="margin-bottom:18px;">
    <select name="school_id" class="pp-select" style="max-width:260px;" onchange="this.form.submit()">
      <option value="">All schools</option>
      @isset($schools)
        @foreach($schools as $s)
          <option value="{{ $s->id }}" {{ request('school_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
      @endisset
    </select>
  </form>

  @if(session('success'))
    <div class="pp-alert pp-alert-success"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>
  @endif
  @if(session('error'))
    <div class="pp-alert pp-alert-danger"><i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span></div>
  @endif

  @forelse($pendingTransactions as $t)
    <div class="pp-case">
      <div class="pp-case-head">
        <div class="pp-who">
          <div class="pp-pupil-name">{{ $t->payment->pupil->name ?? 'Unknown pupil' }}</div>
          <div class="pp-meta">
            <span class="pp-school-tag">{{ $t->payment->pupil->school->name ?? $t->school->name ?? '—' }}</span>
            Submitted {{ $t->created_at->diffForHumans() }}
          </div>
        </div>
        <div class="pp-amount">K{{ number_format($t->amount, 2) }}</div>
      </div>

      <div class="pp-details-grid">
        <div class="item">
          <div class="k">Receipt ref</div>
          <div class="v">{{ $t->receipt_number }}</div>
        </div>
        <div class="item">
          <div class="k">Parent-entered reference</div>
          <div class="v">{{ $t->parent_reference ?: '— none —' }}</div>
        </div>
        <div class="item">
          <div class="k">Proof of payment</div>
          <div class="v">
            @if($t->proof_of_payment_path)
              <a href="{{ asset('storage/' . $t->proof_of_payment_path) }}" target="_blank"><i class="fas fa-file-alt"></i> View file</a>
            @else
              — none —
            @endif
          </div>
        </div>
        <div class="item">
          <div class="k">Balance before</div>
          <div class="v">K{{ number_format($t->payment->balance ?? 0, 2) }}</div>
        </div>
      </div>

      <div class="pp-btn-row">
        <form action="{{ route('admin.payment.verification.approve', $t->id) }}" method="POST" style="flex:1;">
          @csrf
          <button type="submit" class="pp-btn pp-btn-primary"><i class="fas fa-check"></i> Approve &amp; apply</button>
        </form>
        <button type="button" class="pp-btn pp-btn-danger" style="flex:1;" onclick="openReject({{ $t->id }})">
          <i class="fas fa-times"></i> Reject
        </button>
      </div>
    </div>
  @empty
    <div class="pp-empty">
      <div class="big"><i class="fas fa-inbox"></i> Queue is clear</div>
      No manual payments are waiting for review right now.
    </div>
  @endforelse

</div>

<!-- Reject modal -->
<div class="pp-modal-overlay" id="rejectOverlay">
  <div class="pp-modal">
    <h3><i class="fas fa-times-circle"></i> Reject this payment</h3>
    <form id="rejectForm" method="POST">
      @csrf
      <textarea name="rejection_reason" class="pp-textarea" style="width:100%; margin-bottom:14px;"
                placeholder="Why is this being rejected? (shown to staff, not the parent)" required></textarea>
      <div class="pp-modal-actions">
        <button type="button" class="pp-btn pp-btn-ghost" style="width:auto;" onclick="closeReject()">Cancel</button>
        <button type="submit" class="pp-btn pp-btn-danger" style="width:auto; background:#c0392b; color:#fff; border:none;">Confirm reject</button>
      </div>
    </form>
  </div>
</div>

<script>
function openReject(id){
  const template = "{{ route('admin.payment.verification.reject', ['transactionId' => '__ID__']) }}";
  document.getElementById('rejectForm').action = template.replace('__ID__', id);
  document.getElementById('rejectOverlay').classList.add('open');
}
function closeReject(){
  document.getElementById('rejectOverlay').classList.remove('open');
}
</script>
@endsection