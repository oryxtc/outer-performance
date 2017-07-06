@extends('voyager::master')

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i> 查看 {{ ucfirst($dataType->display_name_singular) }}
    </h1>
@stop

@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-bordered" style="padding-bottom:5px;">

                    <!-- /.box-header -->
                    <!-- form start -->


                    @foreach($dataType->readRows as $row)

                        <div class="panel-heading" style="border-bottom:0;">
                            <h3 class="panel-title">{{ $row->display_name }}</h3>
                        </div>

                        <div class="panel-body" style="padding-top:0;">
                            @if($row->field=='status')
                                @if($dataTypeContent->status=='0')
                                    <p>待确认</p>
                                @else
                                    <p>已确认</p>
                                @endif
                            @elseif($row->field=='period_at')
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
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')

@stop
