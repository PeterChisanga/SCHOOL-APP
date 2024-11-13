@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Income Overview</h2>

    <form method="GET" action="{{ route('incomes.index') }}" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <label for="year">Year</label>
                <select name="year" id="year" class="form-control" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="term">Term</label>
                <select name="term" id="term" class="form-control" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="Term 1" {{ request('term') == 'Term 1' ? 'selected' : '' }}>Term 1</option>
                    <option value="Term 2" {{ request('term') == 'Term 2' ? 'selected' : '' }}>Term 2</option>
                    <option value="Term 3" {{ request('term') == 'Term 3' ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-1">Filter</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Term</th>
                <th>Type</th>
                <th>Total Income</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($incomes as $income)
                <tr>
                    <td>{{ $income->term }}</td>
                    <td>{{ ucfirst($income->type) }}</td>
                    <td>K {{ number_format($income->total_income, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
