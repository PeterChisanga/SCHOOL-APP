@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Overview Cards -->
    <div class="row">
        <!-- Number of Students -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $studentsCount }}</h3>
                    <p>Students</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>
        <!-- Number of Teachers -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $teachersCount }}</h3>
                    <p>Teachers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
        </div>
        <!-- Number of Parents -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $parentsCount }}</h3>
                    <p>Parents</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <!-- Number of Classes -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $classesCount }}</h3>
                    <p>Classes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-school"></i>
                </div>
            </div>
        </div>
        <!-- Number of Subjects -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $subjectsCount }}</h3>
                    <p>Subjects</p>
                </div>
                <div class="icon">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bar Chart and Calendar -->
    <div class="row">
        <!-- Bar Chart: Students in each Class -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Number of Students in Each Class</h3>
                </div>
                <div class="card-body">
                    <canvas id="studentsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Calendar</h3>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Data for Bar Chart (replace with actual data)
    var classNames = @json($classNames);  // Example: ['Class 1', 'Class 2', 'Class 3']
    var studentsPerClass = @json($studentsPerClass);  // Example: [30, 25, 20]

    var ctx = document.getElementById('studentsChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: classNames,
            datasets: [{
                label: 'Number of Students',
                data: studentsPerClass,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Calendar initialization
    $(document).ready(function () {
        $('#calendar').datepicker({
            todayHighlight: true,
            autoclose: true
        });
    });
</script>
@endsection

{{-- @section('scripts') --}}
<!-- Chart.js -->

{{-- @endsection --}}
