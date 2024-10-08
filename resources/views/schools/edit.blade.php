@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Edit School Details</h3>
                </div>

                <div class="card-body">
                    <form action="{{ route('schools.update', $school->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="name">School Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $school->name) }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="address">Address</label>
                            <input type="text" name="address" id="address" class="form-control" value="{{ old('address', $school->address) }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone">Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $school->phone) }}" >
                        </div>

                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $school->email) }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="motto">Motto</label>
                            <input type="motto" name="motto" id="motto" class="form-control" value="{{ old('motto', $school->motto) }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="photo">School Logo</label>
                            <input type="file" name="photo" id="photo" class="form-control">
                            @if($school->photo)
                                <img src="{{ asset('storage/' . $school->photo) }}" alt="School Logo" width="100">
                            @endif

                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Update School Details</button>
                            <a href="{{ route('schools.show', $school->id) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>

                    @if($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
