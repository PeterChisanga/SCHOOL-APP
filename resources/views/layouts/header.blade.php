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
                   <h3 class="text-primary">{{ Auth::user()->school->name ?? 'No School Assigned' }}</h3>
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
            <!-- Dashboard -->
            {{-- The routes are implemented using a helper functions (app/helpers) to direct users to their respective dashboards --}}
            <li class="nav-item menu-open">
                <a href="{{ route(dashboardRoute()) }}"
                class="nav-link {{ request()->routeIs(dashboardRoute()) ? 'active' : '' }}">

                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>{{ dashboardLabel() }}</p>
                </a>
            </li>

            <!-- Teachers -->
            @if (Auth::user()->user_type=='admin')
            <li class="nav-item">
                <a href="{{ route('teachers.index') }}" class="nav-link">
                    <i class="nav-icon fas fa-chalkboard-teacher"></i>
                    <p>Teachers</p>
                </a>
            </li>
            @endif

            {{-- seretaries --}}
            @if (Auth::user()->user_type=='admin')
            <li class="nav-item">
                <a href="{{ route('secretaries.index') }}" class="nav-link">
                    <i class="nav-icon fas  fa-user-tie"></i>
                    <p>Secretaries</p>
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

            <!-- Financials -->
            @if (Auth::user()->user_type === 'admin' || Auth::user()->user_type === 'secretary')
                <!-- expenses -->
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
                <li class="nav-item">
                    <a href="{{ route('inventory.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>
                            Inventory
                            <span class="badge bg-warning text-dark ms-2">BETA</span>
                        </p>
                    </a>
                </li>
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
                    <li class="nav-item">
                        <a href="{{ route('subjects.index') }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Subjects</p>
                        </a>
                    </li>
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
                    {{-- <li class="nav-item">
                        <a href="" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Exam Schedule</p>
                        </a>
                    </li> --}}
                </ul>
            </li>

            <!-- settings -->
            @if (Auth::user()->user_type=='admin')
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
                        <a href="{{ route('schools.show',Auth::user()->school->id) }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Customize school details</p>
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            <!-- Communication -->
            {{-- <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-envelope"></i>
                    <p>
                        Communication
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Messages</p>
                        </a>
                    </li>
                </ul>
            </li> --}}

            <!-- My Account -->
            <li class="nav-item">
                <a href="{{ route('users.show')}}" class="nav-link">
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
        </ul>
    </nav>

      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
