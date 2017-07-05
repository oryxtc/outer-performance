<!DOCTYPE html>
<html>
<head>
    <title>@yield('page_title','管理系统')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <!-- Fonts -->
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('fonts/voyager/googleapis.css') }}">
    <!-- CSS Libs -->
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('lib/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('lib/css/animate.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('lib/css/bootstrap-switch.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('lib/css/checkbox3.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('lib/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('lib/css/dataTables.bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('lib/css/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('lib/css/toastr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('lib/css/perfect-scrollbar.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('css/bootstrap-toggle.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('js/icheck/icheck.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('js/datetimepicker/bootstrap-datetimepicker.min.css') }}">
    <!-- CSS App -->
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('css/themes/flat-blue.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('fonts/voyager/googleapis-300.css') }}">
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ voyager_asset('images/logo-icon.png') }}" type="image/x-icon">

    <!-- CSS Fonts -->
    <link rel="stylesheet" href="{{ voyager_asset('fonts/voyager/styles.css') }}">
    <script type="text/javascript" src="{{ voyager_asset('lib/js/jquery.min.js') }}"></script>

    <link rel="stylesheet" href="{{ voyager_asset('css/jquery-ui.css') }}" type="image/x-icon">

    <link rel="stylesheet" href="{{asset('vendor/tcg/voyager/assets/css/fonts-googleapis.css')}}">
    <script type="text/javascript" src="{{ asset('vendor/tcg/voyager/assets/js/jquery-ui.min.js') }}"></script>

    @yield('css')

    <!-- Voyager CSS -->
    <link rel="stylesheet" href="{{ voyager_asset('css/voyager.css') }}">

    <!-- Few Dynamic Styles -->
    <style type="text/css">
        .flat-blue .side-menu .navbar-header, .widget .btn-primary, .widget .btn-primary:focus, .widget .btn-primary:hover, .widget .btn-primary:active, .widget .btn-primary.active, .widget .btn-primary:active:focus{
            background:{{ config('voyager.primary_color','#22A7F0') }};
            border-color:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .breadcrumb a{
            color:{{ config('voyager.primary_color','#22A7F0') }};
        }
    </style>

    @if(!empty(config('voyager.additional_css')))<!-- Additional CSS -->
    @foreach(config('voyager.additional_css') as $css)<link rel="stylesheet" type="text/css" href="{{ asset($css) }}">@endforeach
    @endif

    @yield('head')
</head>

<body class="flat-blue">

<nav class="navbar navbar-default navbar-fixed-top container container-fluid">
    <div class="container">
        <ul class="nav nav-pills">
            <li role="presentation" class="active"><a href="{{route('wechat.home')}}">个人信息</a></li>
            <li role="presentation"><a href="#">Profile</a></li>
            <li role="presentation"><a href="#">Messages</a></li>
        </ul>
    </div>
</nav>

<div class="app-container">
    <div class="fadetoblack visible-xs"></div>
    <div class="row content-container">
    <!-- Main Content -->
        <div class="container-fluid">
            <div class="side-body padding-top">
                @yield('page_header')
                @yield('content')
            </div>
        </div>
    </div>
</div>


<!-- Javascript Libs -->
<script type="text/javascript" src="{{ voyager_asset('lib/js/bootstrap.min.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('lib/js/bootstrap-switch.min.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('lib/js/jquery.matchHeight-min.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('lib/js/jquery.dataTables.min.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('lib/js/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('lib/js/toastr.min.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('lib/js/perfect-scrollbar.jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('js/select2/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('js/bootstrap-toggle.min.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('js/jquery.cookie.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('js/moment-with-locales.min.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('js/datetimepicker/bootstrap-datetimepicker.min.js') }}"></script>
<!-- Javascript -->
<script type="text/javascript" src="{{ voyager_asset('js/readmore.min.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('js/val.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('js/app.js') }}"></script>
<script type="text/javascript" src="{{ voyager_asset('js/helpers.js') }}"></script>
@if(!empty(config('voyager.additional_js')))<!-- Additional Javascript -->
@foreach(config('voyager.additional_js') as $js)<script type="text/javascript" src="{{ asset($js) }}"></script>@endforeach
@endif


@yield('javascript')
</body>
</html>