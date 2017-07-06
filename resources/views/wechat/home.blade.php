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
                        <h3 class="panel-title">个人信息</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    <form role="form"
                          class="form-edit-add"
                          action="{{ route('wechat.updateUserInfo')}}"
                          method="POST" enctype="multipart/form-data">
                        <!-- PUT Method if we are editing -->
                    @if(isset($dataTypeContent->id))
                        {{ method_field("PUT") }}
                    @endif

                    <!-- CSRF TOKEN -->
                        {{ csrf_field() }}

                        <div class="panel-body">
                            <?php $dataTypeRows = $dataType->readRows; ?>
                            @foreach($dataTypeRows as $row)

                                @if($row->field=='role_id' || $row->field=='password' || $row->field=='wechat')

                                @else
                                    <div class="panel-heading" style="border-bottom:0;">
                                        <h3 class="panel-title">{{ $row->display_name }}</h3>
                                    </div>
                                @endif



                                @if($row->field=='role_id' || $row->field=='password' || $row->field=='wechat')

                                @elseif($row->field=='avatar' || $row->field=='id_card_image' || $row->field=='graduate_image'|| $row->field=='wage_image')
                                        <div class="panel-body form-group" style="padding-top:0;" disabled="none">
                                            @include('voyager::multilingual.input-hidden-bread-edit-add')
                                            {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}

                                            @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                                {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                            @endforeach
                                        </div>
                                @else
                                    <div class="panel-body form-group" style="padding-top:0;" disabled="none">
                                        @include('voyager::multilingual.input-hidden-bread-edit-add')
                                        {{--{!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}--}}
                                        {{ $dataTypeContent->{$row->field} }}

                                        @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                            {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach

                        </div><!-- panel-body -->

                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary save">保存照片信息</button>
                        </div>
                    </form>

                    <iframe id="form_target" name="form_target" style="display:none"></iframe>
                    <form id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="post"
                          enctype="multipart/form-data" style="width:0;height:0;overflow:hidden">
                        <input name="image" id="upload_file" type="file"
                               onchange="$('#my_form').submit();this.value='';">
                        <input type="hidden" name="type_slug" id="type_slug" value="{{ $dataType->slug }}">
                        {{ csrf_field() }}
                    </form>

                </div>
            </div>
        </div>
    </div>
@stop


<div class="col-xs-12">

</div>
@section('javascript')
@stop
