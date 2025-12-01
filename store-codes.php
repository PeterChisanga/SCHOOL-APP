//important
//prepare word documents to register pupil information manually
//session should expire after 3 days

To ensure your application is robust, error-free, and ready for deployment, it’s essential to run a set of critical tests. Based on common deployment requirements, here’s a checklist of key test types that would be beneficial before deploying a web application:
1. Unit Tests

    Controllers: Verify that each controller performs the expected actions, such as returning correct views or JSON data.
    Models: Ensure models behave as expected, including relationships and custom functions.
    Services: Test any service classes that encapsulate business logic to confirm they operate as intended.
    Utilities/Helpers: If you have helper functions or utility classes, test them individually to ensure they produce accurate results.

2. Integration Tests

    Database Interactions: Test that database queries and CRUD operations are functioning properly, including inserts, updates, and deletes.
    API Endpoints: Check all API endpoints that the application provides or consumes, validating that they return the correct status codes and data.
    Middleware: Ensure middleware (like authentication, authorization, logging) is functioning as expected across routes.
    File Storage: Verify that any file upload or retrieval process works, particularly if the application allows image or document storage.

3. Feature/Functional Tests

    User Authentication and Authorization: Ensure login, registration, and permissions (e.g., admin vs. teacher access) work correctly.
    Payment Processing: If applicable, run tests to confirm payments are processed, recorded, and displayed accurately.
    Form Submissions: Test all major forms (e.g., student registration, payment submission) for successful and edge-case scenarios.
    Filtering and Sorting: If you have filters (e.g., for students by class or payments by status), check that they work correctly and display results accurately.

4. UI/UX and Front-End Tests

    Basic Visual Layout: Verify that layouts and styles load correctly across different devices and screen sizes.
    JavaScript and AJAX: Ensure that any JavaScript functions, such as AJAX for dynamic content updates, work as expected.
    Accessibility: Confirm that the application meets basic accessibility standards for usability.

5. End-to-End (E2E) Tests

    Primary User Flows: Test critical user workflows from start to finish (e.g., an admin logging in, creating a payment, and generating a report).
    Error and Edge Cases: Simulate scenarios like failed login attempts, unauthorized access, or empty form submissions.

6. Security Tests

    Authentication Bypass: Ensure access control is enforced correctly, and restricted pages can’t be accessed by unauthorized users.
    Input Validation and Sanitization: Test for SQL injection, XSS, and other injection attacks.
    Session Management: Validate that sessions are managed securely, with proper timeout and logout mechanisms.

7. Performance Tests

    Load Testing: Evaluate how the application handles multiple simultaneous users or requests to ensure it won’t crash under high usage.
    Database Query Optimization: Ensure database queries are optimized, especially for large data sets.

8. Regression Tests

    Core Functionality Tests: Run tests on core features to make sure recent changes haven’t introduced new bugs.

9. Final Deployment Tests

    Environment-Specific Configurations: Check configuration files, environment variables, and database connections for the production environment.
    Error Logging: Confirm that logging is set up to capture errors in production.
    Backup and Recovery: Ensure there is a backup plan, especially for the database.

These tests will help identify potential issues before deployment, ensuring the application is both functional and secure for production use.


@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Exam Results for {{ $examResult->pupil->first_name }} {{ $examResult->pupil->last_name }}</h1>
        <a href="{{ route('examResults.exportPdf', $examResult->pupil->id) }}" class="btn btn-primary">Print Exam Results</a>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Mid Term Mark</th>
                <th>End of Term Mark</th>
                <th>Average</th>
                <th>Grade</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($examResult->pupil->examResults as $result)
                @php
                    $average = ($result->mid_term_mark + $result->end_of_term_mark) / 2;

                    // Determine the grade and remark based on the average
                    if ($average >= 75) {
                        $grade = 'A';
                        $remark = 'Excellent';
                    } elseif ($average >= 60) {
                        $grade = 'B';
                        $remark = 'Very Good';
                    } elseif ($average >= 50) {
                        $grade = 'C';
                        $remark = 'Good';
                    } elseif ($average >= 45) {
                        $grade = 'D';
                        $remark = 'Satisfactory';
                    } elseif ($average >= 40) {
                        $grade = 'E';
                        $remark = 'Pass';
                    } else {
                        $grade = 'F';
                        $remark = 'Fail';
                    }
                @endphp

                <tr>
                    <td>{{ $result->subject->name }}</td>
                    <td>{{ $result->mid_term_mark }}</td>
                    <td>{{ $result->end_of_term_mark }}</td>
                    <td>{{ $average }}</td>
                    <td>{{ $grade }}</td>
                    <td>{{ $remark }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Button to Export to PDF -->
    <a href="{{ route('examResults.edit', $examResult->id) }}" class="btn btn-warning">Edit</a>
    <a href="{{ route('examResults.index') }}" class="btn btn-secondary">Back</a>

</div>
@endsection


<!-- Teacher Controller Edited  -->
<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class TeacherController extends Controller {
    public function index() {
        $schoolId = Auth::user()->school_id;
        $teachers = Teacher::where('school_id', $schoolId)->get();

        return view('teachers.index', compact('teachers'));
    }

    public function show($id)
    {
        $teacher = Teacher::with('school', 'class')->findOrFail($id);

        return view('teachers.show', compact('teacher'));
    }

    public function create() {
        $schoolId = Auth::user()->school_id;
        $classes = ClassModel::where('school_id', $schoolId)->get();

        return view('teachers.create', compact('classes'));
    }

    public function store(Request $request) {
        $this->validate($request, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'marital_status' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string',
            'gender' => 'required|string',
            'date_of_birth' => 'required|date',
        ]);

        $userData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone,
            'user_type' => 'teacher',
        ];

        $schoolId = Auth::user()->school_id;
        $userData['school_id'] = $schoolId;

        $user = User::create($userData);

        Teacher::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name ?? null,
            'last_name' => $request->last_name,
            'gender' => $request->gender,
            'marital_status' => $request->marital_status,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address ?? null,
            'date_of_birth' => $request->date_of_birth,
            'admission_date' => $request->admission_date ?? null,
            'qualification' => $request->qualification ?? null,
            'salary' => $request->salary ?? null,
            'school_id' => $schoolId,
            'class_id' => $request->class_id ?? null,
            'user_id' => $user->id,
        ]);

        return redirect()->route('teacherss.index')->with('success', 'Teacher registered successfully.');
    }

    public function edit($id){
        $teacher = Teacher::findOrFail($id);
        $classes = ClassModel::all(); // Fetch available classes
        return view('teachers.edit', compact('teacher', 'classes'));
    }

    public function update(Request $request, Teacher $teacher) {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'marital_status' => 'required|string|max:255',
            'phone' => 'required|string',
            'gender' => 'required|string',
            'date_of_birth' => 'required|date',
            'password' => 'nullable|string|confirmed|min:8',
        ]);

        $teacher->update([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name ?? null,
            'last_name' => $request->last_name,
            'gender' => $request->gender,
            'marital_status' => $request->marital_status,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address ?? null,
            'date_of_birth' => $request->date_of_birth,
            'admission_date' => $request->admission_date ?? null,
            'class_id' => $request->class_id ?? null,
            'qualification' => $request->qualification ?? null,
            'salary' => $request->salary ?? null,
        ]);

        $userData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if ($teacher->user) {
            $teacher->user->update($userData);
        }

        return redirect()->route('teacherss.index')->with('success', 'Teacher updated successfully.');
    }

    public function destroy($id) {
        // Retrieve the teacher with the associated user
        $teacher = Teacher::with('user')->findOrFail($id);

        // Store the user reference before deleting the teacher
        $user = $teacher->user;

        // Delete the teacher
        $teacher->delete();

        // Delete the associated user if it exists
        if ($user) {
            $user->delete();
        }

        return redirect()->route('teacherss.index')->with('success', 'Teacher deleted successfully.');
    }

}

// -----------------Export results code------------------------------------------
<!DOCTYPE html>
<html>
<head>
    <title> Exam Results for {{ $pupil->first_name }} {{ $pupil->last_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .center-text {
            text-align: center;
        }
        h1, h2, h3, h4, p {
            margin: 0;
        }
        .school-logo {
            display: block;
            margin: 0 auto;
            width: 100px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    <div class="center-text">
        @if($school->photo)
            <img src="{{ public_path('storage/' . $school->photo) }}" alt="School Logo" class="school-logo">
        @endif
    </div>

    <div class="center-text">
        <h1>{{ $school->name }}</h1>
        <p><strong>Motto:</strong> {{ $school->motto }}</p>
        <p> {{ $pupil->school->address }}</p>
        <p><strong>Contact:</strong> {{ $pupil->school->phone }} | <strong>Email:</strong> {{ $pupil->school->email }}</p>
    </div>

    <div class="center-text" style="margin-top: 30px;">
        <h2>SCHOOL REPORT FORM</h2>
        <h3> Results for {{ $pupil->first_name }} {{ $pupil->last_name }}</h3>
    </div>
    <p><strong>Class:</strong> {{ $pupil->class->name }} | <strong>Gender:</strong> {{ $pupil->gender }} | <strong>Term:</strong> {{ $term }}  {{ $pupil->examResults->where('term', $term)->last()->created_at->format('Y') }}</p>

    {{-- <p><strong>Class:</strong> {{ $pupil->class->name }} | <strong>Gender:</strong> {{ $pupil->gender }} | <strong>Term:</strong> {{ $term}} {{ $pupil->examResults->created_at }}</p> --}}

    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Mid Term Mark</th>
                <th>End of Term Mark</th>
                <th>Average</th>
                <th>Grade</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @php
                $resultsForTerm = $pupil->examResults->where('term', $term);
            @endphp
            @foreach ($resultsForTerm as $result)
                @php
                    $average = ($result->mid_term_mark + $result->end_of_term_mark) / 2;

                    if ($average >= 75) {
                        $grade = 'A';
                        $remark = 'Excellent';
                    } elseif ($average >= 60) {
                        $grade = 'B';
                        $remark = 'Very Good';
                    } elseif ($average >= 50) {
                        $grade = 'C';
                        $remark = 'Good';
                    } elseif ($average >= 45) {
                        $grade = 'D';
                        $remark = 'Satisfactory';
                    } elseif ($average >= 40) {
                        $grade = 'E';
                        $remark = 'Pass';
                    } else {
                        $grade = 'F';
                        $remark = 'Fail';
                    }
                @endphp

                <tr>
                    <td>{{ $result->subject->name }}</td>
                    <td>{{ $result->mid_term_mark }} %</td>
                    <td>{{ $result->end_of_term_mark }} %</td>
                    <td>{{ $average }} %</td>
                    <td>{{ $grade }}</td>
                    <td>{{ $remark }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Grade Key</h3>
    <table border="1" cellpadding="3" cellspacing="0" width="60%" style="margin: 0 auto; font-size: 12px;">
        <thead>
            <tr>
                <th>Average (%)</th>
                <th>Grade</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>75 and above</td>
                <td>A</td>
                <td>Excellent</td>
            </tr>
            <tr>
                <td>60 - 74</td>
                <td>B</td>
                <td>Very Good</td>
            </tr>
            <tr>
                <td>50 - 59</td>
                <td>C</td>
                <td>Good</td>
            </tr>
            <tr>
                <td>45 - 49</td>
                <td>D</td>
                <td>Satisfactory</td>
            </tr>
            <tr>
                <td>40 - 44</td>
                <td>E</td>
                <td>Pass</td>
            </tr>
            <tr>
                <td>Below 40</td>
                <td>F</td>
                <td>Fail</td>
            </tr>
        </tbody>
    </table>
<br>
<p>Class Teacher's Comment : ..........................................................................Signature: ..................</p>
<br>
<p>Head Teacher's Comment : ...........................................................................Signature: ...................</p>

</body>
</html>

------------------------------------------  export results pdf------------------------------------
<!DOCTYPE html>
<html>
<head>
    <title>Exam Results for {{ $pupil->first_name }} {{ $pupil->last_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .center-text {
            text-align: center;
        }
        h1, h2, h3, h4, p {
            margin: 0;
        }
        .school-logo {
            display: block;
            margin: 0 auto;
            width: 100px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    <div class="center-text">
        @if($school->photo)
            <img src="{{ public_path('storage/' . $school->photo) }}" alt="School Logo" class="school-logo">
        @endif
    </div>

    <div class="center-text">
        <h1>{{ $school->name }}</h1>
        <p><strong>Motto:</strong> {{ $school->motto }}</p>
        <p>{{ $pupil->school->address }}</p>
        <p><strong>Contact:</strong> {{ $pupil->school->phone }} | <strong>Email:</strong> {{ $pupil->school->email }}</p>
    </div>

    <div class="center-text" style="margin-top: 30px;">
        <h2>SCHOOL REPORT FORM</h2>
        <h3>Results for {{ $pupil->first_name }} {{ $pupil->last_name }}</h3>
    </div>
    <p><strong>Class:</strong> {{ $pupil->class->name }} | <strong>Gender:</strong> {{ $pupil->gender }} | <strong>Term:</strong> {{ $term }} {{ $pupil->examResults->where('term', $term)->last()->created_at->format('Y') }} | <strong>Position in Class:</strong> {{ $position ?? '-' }}</p>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Subject</th>
                <th colspan="3" class="text-center">Mid-Term</th>
                <th colspan="3" class="text-center">End of Term</th>
                <th rowspan="2">Average</th>
                <th rowspan="2">Grade</th>
                {{-- <th rowspan="2">Remark</th> --}}
            </tr>
            <tr>
                <th>Mark</th>
                <th>Total Mark</th>
                <th>%</th>
                <th>Mark</th>
                <th>Total Mark</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>
            @php
                $resultsForTerm = $pupil->examResults->where('term', $term);
            @endphp
            @foreach ($resultsForTerm as $result)
                @php
                    $average = ($result->mid_term_mark + $result->end_of_term_mark) / 2;
                    $grade = match(true) {
                        $average >= 75 => 'A',
                        $average >= 60 => 'B',
                        $average >= 50 => 'C',
                        $average >= 45 => 'D',
                        $average >= 40 => 'E',
                        default => 'F'
                    };
                    $remark = match($grade) {
                        'A' => 'Excellent',
                        'B' => 'Very Good',
                        'C' => 'Good',
                        'D' => 'Satisfactory',
                        'E' => 'Pass',
                        'F' => 'Fail'
                    };
                @endphp

                <tr>
                    <td>{{ $result->subject->name }}</td>
                    <td>{{ $result->mid_term_raw ? number_format($result->mid_term_raw, 2) : '-' }}</td>
                    <td>{{ $result->mid_term_max ? number_format($result->mid_term_max, 2) : '-' }}</td>
                    <td>{{ number_format($result->mid_term_mark, 2) }}%</td>
                    <td>{{ $result->end_term_raw ? number_format($result->end_term_raw, 2) : '-' }}</td>
                    <td>{{ $result->end_term_max ? number_format($result->end_term_max, 2) : '-' }}</td>
                    <td>{{ number_format($result->end_of_term_mark, 2) }}%</td>
                    <td>{{ number_format($average, 2) }}%</td>
                    <td>{{ $grade }}</td>
                    {{-- <td>{{ $remark }}</td> --}}
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Grade Key</h3>
    <table border="1" cellpadding="3" cellspacing="0" width="60%" style="margin: 0 auto; font-size: 12px;">
        <thead>
            <tr>
                <th>Average (%)</th>
                <th>Grade</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>75 and above</td>
                <td>A</td>
                <td>Excellent</td>
            </tr>
            <tr>
                <td>60 - 74</td>
                <td>B</td>
                <td>Very Good</td>
            </tr>
            <tr>
                <td>50 - 59</td>
                <td>C</td>
                <td>Good</td>
            </tr>
            <tr>
                <td>45 - 49</td>
                <td>D</td>
                <td>Satisfactory</td>
            </tr>
            <tr>
                <td>40 - 44</td>
                <td>E</td>
                <td>Pass</td>
            </tr>
            <tr>
                <td>Below 40</td>
                <td>F</td>
                <td>Fail</td>
            </tr>
        </tbody>
    </table>
<br>
<p>Class Teacher's Comment: ..........................................................................Signature: ..................</p>
<br>
<p>Head Teacher's Comment: ...........................................................................Signature: ...................</p>

</body>
</html>

//----------------------------------- incomes index blade --------------------
{{-- @extends('layouts.app')
@section('content')
<div class="container">

    {{-- Success message --}}
    @if(session('success'))
        <div id="success-alert" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
        </div>
        <script>
            (function(){
                const el = document.getElementById('success-alert');
                if (!el) return;
                setTimeout(function(){
                    try {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                            new bootstrap.Alert(el).close();
                        } else {
                            el.classList.remove('show');
                            el.remove();
                        }
                    } catch (e) {
                        el.remove();
                    }
                }, 3000);
            })();
        </script>
    @endif
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Income Summary</h2>
        <div>
            <a href="{{ route('incomes.create') }}" class="btn btn-success me-2">+ Add Income</a>

            <!-- PDF Download Button (uses current filters) -->
            <!-- Income Report PDF -->
            <a href="{{ route('incomes.report', request()->only(['term', 'year'])) }}"
            class="btn btn-primary me-2">
                Download Income Report
            </a>

            <!-- Financial Report (Profit & Loss) -->
            <a href="{{ route('financial.report', request()->only(['term', 'year'])) }}"
            class="btn btn-secondary">
                Download Financial Report
            </a>
        </div>
    </div>
    <!-- Filters -->
    <form method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-auto">
                <select name="term" class="form-control">
                    <option value="">All Terms</option>
                    @foreach(['term 1','term 2','term 3'] as $t)
                        <option value="{{ $t }}" {{ $term == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="year" class="form-control">
                    <option value="">All Years</option>
                    @php
                        $years = \App\Models\Payment::where('school_id', auth()->user()->school_id)
                            ->selectRaw('YEAR(created_at) as year')
                            ->union(\App\Models\Income::where('school_id', auth()->user()->school_id)->selectRaw('year'))
                            ->distinct()
                            ->orderBy('year', 'desc')
                            ->pluck('year');
                    @endphp
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Fee Income Summary -->
    @if($feeIncomes->count())
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <strong>Fee-Based Income</strong>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th class="text-end">Amount (ZMW)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feeIncomes as $type => $total)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $type)) }}</td>
                        <td class="text-end"> {{ number_format($total, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="table-active fw-bold">
                        <td>Subtotal (Fees)</td>
                        <td class="text-end">K {{ number_format($feeIncomes->sum(), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Custom Incomes List -->
    @if($customIncomes->count())
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <strong>Other Incomes</strong>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Source</th>
                        <th>Description</th>
                        <th>Term</th>
                        <th class="text-end">Amount (ZMW)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\Income::where('school_id', auth()->user()->school_id)
                        ->when($term, fn($q) => $q->where('term', $term))
                        ->when($year, fn($q) => $q->where('year', $year))
                        ->orderBy('date', 'desc')->get() as $inc)
                        <tr>
                            <td>{{ $inc->date->format('d/m/Y') }}</td>
                            <td>{{ $inc->source }}</td>
                            <td>{{ Str::limit($inc->description, 50) }}</td>
                            <td>{{ $inc->term }}</td>
                            <td class="text-end">{{ number_format($inc->amount, 2) }}</td>
                            <td>
                                <a href="{{ route('incomes.edit', $inc) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('incomes.destroy', $inc) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Delete?')" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="table-active fw-bold">
                        <td>Subtotal (Other Incomes)</td>
                        <td></td>
                        <td class="text-end">K {{ number_format($customIncomes->sum('amount'), 2) }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="alert alert-info">No custom incomes recorded for the selected period.</div>
    @endif

    <!-- Grand Total -->
    <div class="alert alert-dark text-center fs-4 fw-bold">
        Grand Total:K {{ number_format($grandTotal, 2) }}
    </div>
{{-- </div>
@endsection --}}


// -------------------------  results show blade- ---------------------------
{{--
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Exam Results for {{ $examResult->pupil->first_name }} {{ $examResult->pupil->last_name }}</h1>
        <div>
            <!-- Dropdown to export PDF based on term -->
            <div class="dropdown d-inline">
                <button class="btn btn-primary dropdown-toggle" type="button" id="exportPdfDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Print Results
                </button>
                <div class="dropdown-menu" aria-labelledby="exportPdfDropdown">
                    @foreach ($terms as $term)
                        <a class="dropdown-item" href="{{ route('examResults.exportPdf', ['pupil' => $examResult->pupil->id, 'term' => $term]) }}">
                            Export {{ $term }}
                        </a>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('examResults.edit', $examResult->id) }}" class="btn btn-warning ml-2">Edit</a>
            <a href="{{ route('examResults.index') }}" class="btn btn-secondary ml-2">Back</a>
        </div>
    </div>

    @foreach ($terms as $term)
        <div class="card mb-4">
            <div class="card-header">
                <h5>{{ ucfirst($term) }} Results | Position in Class: {{ $positions[$term] ?? '-' }}</h5>
            </div>
            <div class="card-body">
                @php
                    $resultsForTerm = $examResult->pupil->examResults->where('term', $term);
                @endphp

                @if ($resultsForTerm->isEmpty())
                    <p>No results available for {{ ucfirst($term) }}.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th rowspan="2">Subject</th>
                                    <th colspan="3" class="text-center">Mid-Term</th>
                                    <th colspan="3" class="text-center">End of Term</th>
                                    <th rowspan="2">Average</th>
                                    <th rowspan="2">Grade</th>
                                    <th rowspan="2">Remark</th>
                                </tr>
                                <tr>
                                    <th>Mark</th>
                                    <th>Total Mark</th>
                                    <th>Percentage</th>
                                    <th>Mark</th>
                                    <th>Total Mark</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resultsForTerm as $result)
                                    @php
                                        $average = ($result->mid_term_mark + $result->end_of_term_mark) / 2;
                                        $grade = match(true) {
                                            $average >= 75 => 'A',
                                            $average >= 60 => 'B',
                                            $average >= 50 => 'C',
                                            $average >= 45 => 'D',
                                            $average >= 40 => 'E',
                                            default => 'F'
                                        };
                                        $remark = match($grade) {
                                            'A' => 'Excellent',
                                            'B' => 'Very Good',
                                            'C' => 'Good',
                                            'D' => 'Satisfactory',
                                            'E' => 'Pass',
                                            'F' => 'Fail'
                                        };
                                    @endphp

                                    <tr>
                                        <td>{{ $result->subject->name }}</td>
                                        <td>{{ $result->mid_term_raw ?? '-' }}</td>
                                        <td>{{ $result->mid_term_max ?? '-' }}</td>
                                        <td>{{ number_format($result->mid_term_mark, 2) }}%</td>
                                        <td>{{ $result->end_term_raw ?? '-' }}</td>
                                        <td>{{ $result->end_term_max ?? '-' }}</td>
                                        <td>{{ number_format($result->end_of_term_mark, 2) }}%</td>
                                        <td>{{ number_format($average, 2) }}%</td>
                                        <td>{{ $grade }}</td>
                                        <td>{{ $remark }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection --}}

