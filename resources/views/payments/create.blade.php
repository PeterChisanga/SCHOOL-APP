@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Record Payment for {{ $pupil->first_name }} {{ $pupil->last_name }}</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('payments.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="mode_of_payment">Mode of Payment <span class="text-danger">*</span></label>
            <select name="mode_of_payment" id="mode_of_payment" class="form-control" required>
                <option value="">Select Mode of Payment</option>
                <option value="cash"          {{ old('mode_of_payment') == 'cash'          ? 'selected' : '' }}>Cash</option>
                <option value="deposit"       {{ old('mode_of_payment') == 'deposit'       ? 'selected' : '' }}>Deposit</option>
                <option value="AirtelMoney"   {{ old('mode_of_payment') == 'AirtelMoney'   ? 'selected' : '' }}>Airtel Money</option>
                <option value="MtnMoney"      {{ old('mode_of_payment') == 'MtnMoney'      ? 'selected' : '' }}>MTN Mobile Money</option>
                <option value="ZamtelKwacha"  {{ old('mode_of_payment') == 'ZamtelKwacha'  ? 'selected' : '' }}>Zamtel Kwacha</option>
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
            <label for="type">Payment Type <span class="text-danger">*</span></label>
            <select class="form-control" id="type" name="type" required>
                <option value="">Select Payment Type</option>
                <option value="School Fees"   {{ old('type') == 'School Fees'   ? 'selected' : '' }}>School Fees</option>
                <option value="Lunch"         {{ old('type') == 'Lunch'         ? 'selected' : '' }}>Lunch</option>
                <option value="Transport Fee" {{ old('type') == 'Transport Fee' ? 'selected' : '' }}>Transport Fee</option>
                <option value="Uniform"       {{ old('type') == 'Uniform'       ? 'selected' : '' }}>Uniform</option>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Total Amount <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
        </div>

        <div class="form-group">
            <label for="amount_paid">Amount Paid <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="amount_paid" name="amount_paid" value="{{ old('amount_paid') }}" required>
        </div>

        <input type="hidden" name="pupil_id" value="{{ old('pupil_id') ?? $pupil->id }}">

        <div class="form-group">
            <label for="term">Term <span class="text-danger">*</span></label>
            <select class="form-control" name="term" id="term" required>
                <option value="">Select Term</option>
                <option value="Term 1" {{ old('term') == 'Term 1' ? 'selected' : '' }}>Term 1 (First Term)</option>
                <option value="Term 2" {{ old('term') == 'Term 2' ? 'selected' : '' }}>Term 2 (Second Term)</option>
                <option value="Term 3" {{ old('term') == 'Term 3' ? 'selected' : '' }}>Term 3 (Third Term)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="date">Payment Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="date" name="date" value="{{ old('date') }}" required>
        </div>

        <div class="form-group">
            <label for="deposit_slip_id">Deposit Slip ID</label>
            <input type="text" class="form-control" id="deposit_slip_id" name="deposit_slip_id" value="{{ old('deposit_slip_id') }}">
        </div>

        <button type="submit" class="btn btn-primary">Record Payment</button>
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
    toggleMomoFields(); // run on page load to restore old() state
</script>
@endpush
@endsection