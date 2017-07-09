<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <title>考勤详情</title>

    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('lib/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('lib/css/bootstrap-switch.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ voyager_asset('css/bootstrap-toggle.min.css') }}">
</head>
<body>
<nav class="navbar navbar-default navbar-fixed-top container container-fluid ">
    <div class="container">
        <ul class="nav nav-pills">
            <li role="presentation" class="nav-item nav-link" dta-toggle="pill">
                <a href="{{route('wechat.home')}}">个人信息</a>
            </li>
            <li role="presentation" class="nav-item nav-link" dta-toggle="pill">
                <a href="{{route('wechat.getAttendanceList')}}">考勤列表</a>
            </li>
            <li role="presentation" class="nav-item nav-link" dta-toggle="pill">
                <a href="{{route('wechat.getWageList')}}">工资列表</a>
            </li>
        </ul>
    </div>
</nav>
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <!-- /.box-header -->
                <!-- form start -->
                <div class="list-group" id="app">
                    <info attendanceid="{{$id}}"></info>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>

{{--打包js--}}
<script type="text/javascript" src="{{ asset('js/app.js') }}"></script>

