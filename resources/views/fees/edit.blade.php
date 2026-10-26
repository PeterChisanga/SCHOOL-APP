@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Fee</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('fees.update', $fee) }}">
        @csrf
        @method('PUT')
        @include('fees._form')

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('fees.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
