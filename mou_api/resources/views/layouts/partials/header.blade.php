<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title> {{ strip_tags(config('app.name')) }} - @yield('page_title', 'Your title here') </title>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset (mix('/css/app.css') ) }}" rel="stylesheet" type="text/css" />
    @yield('css')
</head>
