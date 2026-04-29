@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Record Payment of the balance for {{ $payment->pupil->first_name }} {{ $payment->pupil->last_name }}</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('payments.pay-balance', $payment) }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="amount_paid">Amount Paid</label>
            <input type="number" class="form-control" id="amount_paid" name="amount_paid"
                   value="{{ old('amount_paid') }}" required>
        </div>

        <div class="form-group">
            <label for="mode_of_payment">Mode of Payment</label>
            <select name="mode_of_payment" id="mode_of_payment" class="form-control" required>
                <option value="">Select Mode of Payment</option>
                <option value="cash"         {{ old('mode_of_payment') == 'cash'         ? 'selected' : '' }}>Cash</option>
                <option value="deposit"      {{ old('mode_of_payment') == 'deposit'      ? 'selected' : '' }}>Deposit</option>
                <option value="AirtelMoney"  {{ old('mode_of_payment') == 'AirtelMoney'  ? 'selected' : '' }}>Airtel Money</option>
                <option value="MtnMoney"     {{ old('mode_of_payment') == 'MtnMoney'     ? 'selected' : '' }}>MTN Mobile Money</option>
                <option value="ZamtelKwacha" {{ old('mode_of_payment') == 'ZamtelKwacha' ? 'selected' : '' }}>Zamtel Kwacha</option>
            </select>
        </div>

        {{-- Mobile Money fields (shown/hidden by JS) --}}
        <div id="momo-fields" style="display:none;">
            <div class="form-group">
                <label for="account_number">Mobile Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="account_number" name="account_number"
                       placeholder="e.g. 260971234567" value="{{ old('account_number') }}">
                <small class="text-muted">Enter the number linked to the mobile wallet (e.g. 260971234567)</small>
            </div>
            <div class="form-group">
                <label for="email">Email (optional)</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
            </div>
        </div>

        <div class="form-group">
            <label for="date">Payment Date</label>
            <input type="date" class="form-control" id="date" name="date" value="{{ old('date') }}" required>
        </div>

        <div class="form-group">
            <label for="deposit_slip_id">Deposit Slip ID (optional)</label>
            <input type="text" class="form-control" id="deposit_slip_id" name="deposit_slip_id"
                   value="{{ old('deposit_slip_id') }}">
        </div>

        <a href="{{ route('payments.show', $payment->id) }}" class="btn btn-secondary btn-sm">Back</a>
        <button type="submit" class="btn btn-primary">Record Payment of the balance</button>
    </form>
</div>

@push('scripts')
<script>
    const momoOptions = ['AirtelMoney', 'MtnMoney', 'ZamtelKwacha'];
    const momoFields  = document.getElementById('momo-fields');
    const modeSelect  = document.getElementById('mode_of_payment');

    function toggleMomoFields() {
        momoFields.style.display = momoOptions.includes(modeSelect.value) ? 'block' : 'none';
    }

    modeSelect.addEventListener('change', toggleMomoFields);
    toggleMomoFields();
</script>
@endpush
@endsection