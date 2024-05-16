<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">@yield('contentheader_title', 'Page Header title')</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    @section('breadcrumbs')
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
{{--                    <li class="breadcrumb-item active">Starter Page</li>--}}
                    @show
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
