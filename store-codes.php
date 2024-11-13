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
