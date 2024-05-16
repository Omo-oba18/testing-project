<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layouts.partials.header')
<body class="hold-transition sidebar-mini layout-navbar-fixed layout-fixed accent-primary">
<div class="wrapper">
    <!-- Navbar -->
    @include('layouts.partials.navbar')
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    @include('layouts.partials.sidebar')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        @include('layouts.partials.contentheader')
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                @if (session()->has('flash_message'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fa fa-fw fa-check"></i> {{ session()->get('flash_message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>

                    </div>
                @endif
                @if (session()->has('status'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fa fa-fw fa-check"></i> {{ session()->get('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>

                    </div>
                @endif
                @if (session()->has('flash_info'))
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="fa fa-fw fa-check"></i> {{ session()->get('flash_info') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif

                @yield('main-content')

            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <!-- Control Sidebar -->
    @include('layouts.partials.sidebarcontrol')
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    @include('layouts.partials.footer')
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
@include('layouts.partials.scripts')
@yield('scripts')

</body>
</html>
