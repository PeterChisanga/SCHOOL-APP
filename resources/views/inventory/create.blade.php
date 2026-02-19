@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Add Inventory Item</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('inventory.store') }}" method="POST">
        @csrf

        <div class="form-group mb-3">
            <label for="name">Item Name</label>
            <input type="text" name="name" id="name"
                class="form-control"
                value="{{ old('name') }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="inventory_category_id">Category</label>
            <select name="inventory_category_id" class="form-control" required>
                <option value="">Select Category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('inventory_category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="quantity">Quantity</label>
            <input type="number" name="quantity" class="form-control"
                value="{{ old('quantity') }}" min="0" required>
        </div>

        <div class="form-group mb-3">
            <label for="condition">Condition</label>
            <input type="text" name="condition" class="form-control"
                value="{{ old('condition') }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="location">Location</label>
            <input type="text" name="location" class="form-control"
                value="{{ old('location') }}">
        </div>

        <div class="form-group mb-3">
            <label for="date_added">Date Added</label>
            <input type="date" name="date_added" class="form-control"
                value="{{ old('date_added') }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Add Item</button>
    </form>
</div>
@endsection
