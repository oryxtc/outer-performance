@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i> @if(isset($dataTypeContent->id)){{ '修改' }}@else{{ '新增' }}@endif {{ $dataType->display_name_singular }}
    </h1>
@stop

@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-bordered">

                    <div class="panel-heading">
                        <h3 class="panel-title">@if(isset($dataTypeContent->id)){{ '修改' }}@else{{ '新增' }}@endif {{ $dataType->display_name_singular }}</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    <form role="form"
                          class="form-edit-add"
                          action="@if(isset($dataTypeContent->id)){{ route('voyager.'.$dataType->slug.'.update', $dataTypeContent->id) }}@else{{ route('voyager.'.$dataType->slug.'.store') }}@endif"
                          method="POST" enctype="multipart/form-data">
                        <!-- PUT Method if we are editing -->
                    @if(isset($dataTypeContent->id))
                        {{ method_field("PUT") }}
                    @endif

                    <!-- CSRF TOKEN -->
                        {{ csrf_field() }}

                        <div class="panel-body">

                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                        <!-- If we are editing -->
                            @if(isset($dataTypeContent->id))
                                <?php $dataTypeRows = $dataType->editRows; ?>
                            @else
                                <?php $dataTypeRows = $dataType->addRows; ?>
                            @endif

                            @foreach($dataTypeRows as $row)
                                @if($row->field=='role_id')
                                    <label for="name">{{ $row->display_name }}</label>
                                    @include('voyager::multilingual.input-hidden-bread-edit-add')
                                    {{--{!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}--}}
                                    <select name="role_id" id="role" class="form-control">
                                        <?php $roles = TCG\Voyager\Models\Role::orderBy('id','DESC')->get(); ?>
                                        @foreach($roles as $role)
                                            <option value="{{$role->id}}"
                                                    @if(isset($dataTypeContent) && $dataTypeContent->role_id == $role->id) selected @endif>{{$role->display_name}}</option>
                                        @endforeach
                                    </select>
                                    @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                        {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                    @endforeach
                                @else
                                    <div class="form-group @if($row->type == 'hidden') hidden @endif">
                                        <label for="name">{{ $row->display_name }}</label>
                                        @include('voyager::multilingual.input-hidden-bread-edit-add')
                                        {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}

                                        @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                            {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach

                        </div><!-- panel-body -->

                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary save">保存</button>
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

@section('javascript')
    <script>
        $('document').ready(function () {
            $('.toggleswitch').bootstrapToggle();
        });
    </script>
    <script src="{{ voyager_asset('lib/js/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ voyager_asset('js/voyager_tinymce.js') }}"></script>
@stop
