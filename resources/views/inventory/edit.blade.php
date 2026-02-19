@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Inventory Item</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('inventory.update', $inventory->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label>Item Name</label>
            <input type="text" name="name" class="form-control"
                value="{{ old('name', $inventory->item_name) }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Category</label>
            <select name="inventory_category_id" class="form-control" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('inventory_category_id', $inventory->inventory_category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control"
                value="{{ old('quantity', $inventory->quantity) }}" min="0" required>
        </div>

        <div class="form-group mb-3">
            <label>Condition</label>
            <input type="text" name="condition" class="form-control"
                value="{{ old('condition', $inventory->condition) }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control"
                value="{{ old('location', $inventory->location) }}">
        </div>

        <div class="form-group mb-3">
            <label>Date Added</label>
            <input type="date" name="date_added" class="form-control"
                value="{{ old('date_added', $inventory->date_added) }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Adjustment Note (Optional)</label>
            <textarea name="note" class="form-control">{{ old('note') }}</textarea>
        </div>

        <button type="submit" class="btn btn-warning">Update Item</button>
    </form>
</div>
@endsection
