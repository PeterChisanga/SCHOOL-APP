@extends('layouts.app')

@section('content')
<div class="container">
    {{-- <div class="row justify-content-center">
        <div class="col-md-10"> --}}
            <div class="card">
                <div class="card-header">{{ __('Register Pupil') }}</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('pupils.store') }}">
                        @csrf

                        <div class="row">
                            {{-- Left Column --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">{{ __('First Name') }}<span class="text-danger">*</span></label>
                                    <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name') }}" required autofocus>
                                    @error('first_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="middle_name">{{ __('Middle Name') }}</label>
                                    <input id="middle_name" type="text" class="form-control @error('middle_name') is-invalid @enderror" name="middle_name" value="{{ old('middle_name') }}">
                                    @error('middle_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="last_name">{{ __('Last Name') }}<span class="text-danger">*</span></label>
                                    <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name') }}" required>
                                    @error('last_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="gender">{{ __('Gender') }} <span class="text-danger">*</span></label>
                                    <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror" required>
                                        <option value="" disabled selected>Select Gender</option>
                                        <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Male</option>
                                        <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="class_id">{{ __('Class') }}<span class="text-danger">*</span></label>
                                    <select id="class_id" class="form-control @error('class_id') is-invalid @enderror" name="class_id" required>
                                        <option value="">{{ __('Select Class') }}</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>


                            </div>

                            {{-- Right Column --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_of_birth">{{ __('Date of Birth') }}<span class="text-danger">*</span></label>
                                    <input id="date_of_birth" type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                                    @error('date_of_birth')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </spn>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="admission_date">{{ __('Date of Admission') }}</label>
                                    <input id="admission_date" type="date" class="form-control @error('admission_date') is-invalid @enderror" name="admission_date" value="{{ old('admission_date') }}" >
                                    @error('admission_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </spn>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="religion">{{ __('Religion') }}<span class="text-danger">*</span></label>
                                    <input id="religion" type="text" class="form-control @error('religion') is-invalid @enderror" name="religion" value="{{ old('religion') }}" required>
                                    @error('religion')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="blood_group">{{ __('Blood Group') }}</label>
                                    <input id="blood_group" type="text" class="form-control @error('blood_group') is-invalid @enderror" name="blood_group" value="{{ old('blood_group') }}" >
                                    @error('blood_group')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="health_conditions">{{ __('Health Conditions') }}</label>
                                    <textarea id="health_conditions" class="form-control @error('health_conditions') is-invalid @enderror" name="health_conditions" value="{{ old('health_conditions') }}" cols="10" rows="5" placeholder="If a student has health conditions write them here"></textarea>
                                    @error('health_conditions')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mt-4">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register Pupil') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        {{-- </div>
    </div> --}}
</div>
@endsection
