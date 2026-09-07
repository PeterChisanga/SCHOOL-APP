@auth
@php
    $isPlatformAdmin = Auth::user()->user_type === 'platform_admin';
    $actingSchoolId = $isPlatformAdmin ? session('acting_school_id') : Auth::user()->school_id;
    $actingAsAdmin = Auth::user()->user_type === 'admin' || ($isPlatformAdmin && $actingSchoolId);
@endphp
<!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        </ul>

         <ul class="navbar-nav mx-auto">
            <li class="nav-item">
                <span class="nav-link">
                   <h3 class="text-primary">
                        @if ($isPlatformAdmin && ! $actingSchoolId)
                            Platform Admin
                        @elseif ($isPlatformAdmin && $actingSchoolId)
                            {{ optional(\App\Models\School::find($actingSchoolId))->name ?? 'Unknown School' }} <small class="text-muted">(viewing as Platform Admin)</small>
                        @else
                            {{ Auth::user()->school->name ?? 'No School Assigned' }}
                        @endif
                   </h3>
                </span>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
            <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
        </ul>
    </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="" class="brand-link">
      <span class="brand-text font-weight-light">E-school</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        @if ($isPlatformAdmin && ! $actingSchoolId)
            <!-- Platform Admin -->
            <li class="nav-item menu-open">
                <a href="{{ route('admin.reconciliation.index') }}" class="nav-link active">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Reconciliation</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('schools.index') }}" class="nav-link">
                    <i class="nav-icon fas fa-school"></i>
                    <p>Schools</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link">
                    <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
                    <p class="text-danger">Logout</p>
                </a>
            </li>
        @else
            @if ($isPlatformAdmin)
            <!-- Exit impersonation -->
            <li class="nav-item">
                <a href="{{ route('schools.exit') }}" class="nav-link" style="background:#dc3545;">
                    <i class="nav-icon fas fa-arrow-left"></i>
                    <p>Exit to Platform Admin</p>
                </a>
            </li>
            @endif

            <!-- Dashboard -->
            <li class="nav-item menu-open">
                <a href="{{ route('admin.dashboard') }}" class="nav-link active">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    @if ($actingAsAdmin)
                    <p>Admin Dashboard</p>
                    @elseif (Auth::user()->user_type=='teacher')
                    <p>Teacher Dashboard</p>
                    @else
                    <p>Secretary Dashboard</p>
                    @endif
                </a>
            </li>

            <!-- Teachers -->
            @if ($actingAsAdmin)
            <li class="nav-item">
                <a href="{{ route('teachers.index') }}" class="nav-link">
                    <i class="nav-icon fas fa-chalkboard-teacher"></i>
                    <p>Teachers</p>
                </a>
            </li>
            @endif

            {{-- secretaries --}}
            @if ($actingAsAdmin)
            <li class="nav-item">
                <a href="{{ route('secretaries.index') }}" class="nav-link">
                    <i class="nav-icon fas fa-user-tie"></i>
                    <p>Secretaries</p>
                </a>
            </li>
            @endif

            {{-- users / roles --}}
            @if ($actingAsAdmin)
            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link">
                    <i class="nav-icon fas fa-user-cog"></i>
                    <p>Manage Users</p>
                </a>
            </li>
            @endif

            <!-- Pupils -->
            <li class="nav-item">
                <a href="{{ route('pupils.index') }}" class="nav-link">
                    <i class="nav-icon fas fa-user-graduate"></i>
                    <p>Pupils</p>
                </a>
            </li>

            <!-- Parents -->
            <li class="nav-item">
                <a href="{{ route('parents.index') }}" class="nav-link">
                    <i class="nav-icon fas fa-users"></i>
                    <p>Parents</p>
                </a>
            </li>

            <!-- Expenses -->
            @if ($actingAsAdmin)
            <li class="nav-item">
                <a href="{{ route('expenses.index') }}" class="nav-link">
                    <i class="nav-icon fas fa-money-bill-wave"></i>
                    <p>Expenses</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('incomes.index') }}" class="nav-link">
                    <i class="nav-icon fas fa-chart-line"></i>
                    <p>Incomes</p>
                </a>
            </li>
            @endif

            <!-- Fees -->
            @if ($actingAsAdmin || Auth::user()->user_type === 'secretary')
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-dollar-sign"></i>
                    <p>
                        Fee Collection
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('payments.index') }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Fee Collections</p>
                        </a>
                    </li>

                    @if ($actingAsAdmin)
                    <li class="nav-item">
                        <a href="{{ route('reconciliation.index') }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Platform Reconciliation</p>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

            <!-- Academics -->
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-book"></i>
                    <p>
                        Academics
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @if ($actingAsAdmin)
                    <li class="nav-item">
                        <a href="{{ route('subjects.index') }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Subjects</p>
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('classes.index') }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Classes</p>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Examinations -->
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-clipboard"></i>
                    <p>
                        Examinations
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('examResults.index') }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Exam Results</p>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Settings -->
            @if ($actingAsAdmin)
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-cog"></i>
                    <p>
                        Settings
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('schools.show', $actingSchoolId) }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Customize school details</p>
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            <!-- Parent Portal -->
            <li class="nav-item">
                <a href="{{ route('parent.search.page') }}" class="nav-link">
                    <i class="nav-icon fas fa-users"></i>
                    <p>Parent Portal</p>
                </a>
            </li>
            
           
            <!-- My Account -->
            <li class="nav-item">
                <a href="{{ route('users.show') }}" class="nav-link">
                    <i class="nav-icon fas fa-user"></i>
                    <p>My Account</p>
                </a>
            </li>

            <!-- Logout -->
            <li class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link">
                    <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
                    <p class="text-danger">Logout</p>
                </a>
            </li>
        @endif
        </ul>
    </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
@endauth