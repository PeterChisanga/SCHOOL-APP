@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">School Inventory</h2>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('inventory.create') }}" class="btn btn-primary mb-3">
        Add Inventory Item
    </a>

    <!-- Filter & Search -->
    <form method="GET" action="{{ route('inventory.index') }}" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <select name="category" class="form-control"   onchange="this.form.submit()">
                    <option value="">-- Filter by Category --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </div>
    </form>

    @if ($inventories->isEmpty())
        <p>No inventory items found.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Condition</th>
                        <th>Location</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inventories as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->category->name ?? '-' }}</td>
                            <td>
                                @if($item->quantity <= 5)
                                    <span class="badge bg-danger">
                                        {{ $item->quantity }} (Low)
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        {{ $item->quantity }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ $item->condition }}</td>
                            <td>{{ $item->location ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->date_added)->format('d/m/Y') }}</td>
                            <td>
                                <!-- Stock Buttons -->
                                <!-- Add Stock Button -->
                                <button class="btn btn-success btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addStockModal{{ $item->id }}">
                                    +
                                </button>

                                <!-- Remove Stock Button -->
                                <button class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#removeStockModal{{ $item->id }}">
                                    -
                                </button>

                                <!-- Activity -->
                                <a href="{{ route('inventory.activity', $item->id) }}"
                                class="btn btn-info btn-sm">
                                    Log
                                </a>

                                <!-- Edit -->
                                <a href="{{ route('inventory.edit', $item->id) }}"
                                class="btn btn-warning btn-sm">
                                Edit
                                </a>

                                <!-- Delete -->
                                {{-- <form action="{{ route('inventory.destroy', $item->id) }}"
                                    method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure?')">
                                        Delete
                                    </button>
                                </form> --}}
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @foreach($inventories as $item)
        {{-- Add Stock Modal --}}
        <div class="modal fade" id="addStockModal{{ $item->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <form method="POST" action="{{ route('inventory.addStock', $item->id) }}">
                        @csrf

                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                Add Stock — {{ $item->item_name }}
                            </h5>
                            <button type="button" class="btn-close"
                                    data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <div class="form-group mb-3">
                                <label>Quantity to Add</label>
                                <input type="number"
                                    name="amount"
                                    class="form-control"
                                    min="1"
                                    required>
                            </div>

                            <div class="form-group">
                                <label>Note (Optional)</label>
                                <textarea name="note"
                                        class="form-control"
                                        rows="3"
                                        placeholder="e.g Supplier delivery batch 3"></textarea>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="submit"
                                    class="btn btn-success">
                                Confirm Add
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- Remove Stock Modal --}}
        <div class="modal fade" id="removeStockModal{{ $item->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <form method="POST" action="{{ route('inventory.removeStock', $item->id) }}">
                        @csrf

                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                Remove Stock — {{ $item->item_name }}
                            </h5>
                            <button type="button" class="btn-close"
                                    data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <div class="form-group mb-3">
                                <label>Quantity to Remove</label>
                                <input type="number"
                                    name="amount"
                                    class="form-control"
                                    min="1"
                                    max="{{ $item->quantity }}"
                                    required>
                                <small class="text-muted">
                                    Available: {{ $item->quantity }}
                                </small>
                            </div>

                            <div class="form-group">
                                <label>Reason (Optional)</label>
                                <textarea name="note"
                                        class="form-control"
                                        rows="3"
                                        placeholder="e.g Issued to Grade 7 class"></textarea>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="submit"
                                    class="btn btn-danger">
                                Confirm Remove
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection
