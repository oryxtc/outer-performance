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
                        <h3 class="panel-title">工资详情</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    <div class="list-group">
                        @foreach($dataType->readRows as $row)
                            @if($row->field=='status' || $row->field=='social_security_company'|| $row->field=='provident_fund_company'|| $row->field=='total_company'|| $row->field=='remark' || $row->field=='created_at')

                            @else
                                <div class="panel-heading" style="border-bottom:0;">
                                    <h3 class="panel-title">{{ $row->display_name }}</h3>
                                </div>

                                <div class="panel-body" style="padding-top:0;">
                                    @if($row->field=='period_at')
                                        <p>{{date('Y-m',strtotime($dataTypeContent->period_at))}}</p>
                                    @elseif($row->type == "image")
                                        <img style="max-width:640px"
                                             src="{!! Voyager::image($dataTypeContent->{$row->field}) !!}">
                                    @elseif($row->type == 'date')
                                        {{ \Carbon\Carbon::parse($dataTypeContent->{$row->field})->format('F jS, Y h:i A') }}
                                    @elseif($row->field=='role_id')
                                        @if($dataTypeContent->role)
                                            {{$dataTypeContent->role->display_name}}
                                        @endif
                                    @else
                                        <p>{{ $dataTypeContent->{$row->field} }}</p>
                                    @endif
                                </div><!-- panel-body -->
                                @if(!$loop->last)
                                    <hr style="margin:0;">
                                @endif
                            @endif
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
