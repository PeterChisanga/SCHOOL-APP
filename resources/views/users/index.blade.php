@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Manage Users</h2>
    <p class="text-muted">Login accounts for your school's staff. Change a user's role below.</p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Current Role</th>
                    <th>Change Role</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                        <td>{{ $user->email ?? '—' }}</td>
                        <td>{{ $user->phone_number }}</td>
                        <td><span class="badge badge-primary">{{ ucfirst($user->user_type) }}</span></td>
                        <td>
                            @if ($user->id === auth()->id())
                                <span class="text-muted">Cannot change your own role</span>
                            @elseif (in_array($user->user_type, ['admin', 'secretary', 'teacher']))
                                <form action="{{ route('users.update-type', $user->id) }}" method="POST" class="form-inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="user_type" class="form-control form-control-sm mr-2">
                                        @foreach (['admin' => 'Admin', 'secretary' => 'Secretary', 'teacher' => 'Teacher'] as $value => $label)
                                            <option value="{{ $value }}" {{ $user->user_type === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                </form>
                            @else
                                <span class="text-muted">Not editable here</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No users found for your school.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
