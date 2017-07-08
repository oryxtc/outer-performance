@extends('wechat.master')

@section('page_title','个人信息')

@section('page_header')
@stop

@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-bordered">

                    <div class="panel-heading">
                        <a class="btn btn-primary panel-title text-center" href="{{route('wechat.showApplyAttendance')}}">申请考勤</a>
                        <h3 class="panel-title" style="width: 40%;">考勤列表</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    <div class="list-group">
                        @foreach($data as $key=>$item)
                            <a href="{{route('wechat.attendanceInfo',['id'=>$item['id']])}}"
                               class="list-group-item ">
                                {{$item['title']}}
                                <span style="margin-right: 10px" class="pull-right">状态:{{$item['status']}}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


<div class="col-xs-12">

</div>
@section('javascript')
@stop
