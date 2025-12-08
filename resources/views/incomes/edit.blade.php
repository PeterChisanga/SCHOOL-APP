@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Income</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('incomes.update', $income) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" min="0.0" value="{{ old('amount', $income->amount) }}" required>
        </div>

        <div class="form-group">
            <label for="source">Source</label>
            <input type="text" name="source" id="source" class="form-control" value="{{ old('source', $income->source) }}" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control">{{ old('description', $income->description) }}</textarea>
        </div>

        <div class="form-group">
            <label for="term">Term</label>
            <select name="term" id="term" class="form-control" required>
                <option value="term 1" {{ old('term', $income->term) == 'term 1' ? 'selected' : '' }}>Term 1</option>
                <option value="term 2" {{ old('term', $income->term) == 'term 2' ? 'selected' : '' }}>Term 2</option>
                <option value="term 3" {{ old('term', $income->term) == 'term 3' ? 'selected' : '' }}>Term 3</option>
            </select>
        </div>

        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" name="date" id="date" class="form-control" value="{{ old('date', $income->date->format('Y-m-d')) }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Income</button>
        <a href="{{ route('incomes.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
