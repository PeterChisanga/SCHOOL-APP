@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Add Income</h2>
    <form method="POST" action="{{ route('incomes.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Amount<span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Source<span class="text-danger">*</span></label>
                <input type="text" name="source" class="form-control" placeholder="e.g., Donation, CDF, Fundraising..." required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Term <span class="text-danger">*</span></label>
                <select name="term" class="form-control" required>
                    <option value="term 1">Term 1</option>
                    <option value="term 2">Term 2</option>
                    <option value="term 3">Term 3</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label>Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" required>
            </div>
        </div>
        <div class="mb-3">
            <label>Description (Optional)</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Income</button>
        <a href="{{ route('incomes.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
