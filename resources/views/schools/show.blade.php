@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="card-header">
                    <h3>School Details</h3>
                </div>

                <div class="card-body">
                    @if($school->photo)
                        <img src="{{ asset('storage/' . $school->photo) }}" alt="School Logo" width="100">
                    @endif
                    <h4> {{ $school->name }}</h4>
                    <p><strong>Motto:</strong> {{ $school->motto }}</p>
                    <p><strong>Address:</strong> {{ $school->address }}</p>
                    <p><strong>Contact:</strong> {{ $school->phone }}</p>
                    <p><strong>Email:</strong> {{ $school->email }}</p>
                    <p><strong>Created At:</strong> {{ $school->created_at->format('d M, Y') }}</p>

                    <hr>
                    <h4>Associated Users</h4>
                    @if ($school->users->count() > 0)
                        <ul>
                            @foreach ($school->users as $user)
                                <li>
                                    {{ $user->first_name }} ({{ $user->email }})
                                    @if($user->role)
                                        - <span>{{ ucfirst($user->role) }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>No users associated with this school.</p>
                    @endif

                    <hr>
                    <a href="{{ route('schools.edit', $school->id) }}" class="btn btn-primary">Edit School Details</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
