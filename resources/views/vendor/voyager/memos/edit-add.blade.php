@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@if(isset($dataTypeContent->id))
    @section('page_title','修改 '.$dataType->display_name_singular)
@else
    @section('page_title','新增 '.$dataType->display_name_singular)
@endif

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i> @if(isset($dataTypeContent->id)){{ '修改' }}@else{{ '新增' }}@endif {{ $dataType->display_name_singular }}
    </h1>
    @include('voyager::multilingual.language-selector')
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

                        <!-- Modal -->
                            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" data-type=""
                                 aria-labelledby="myModalLabel">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title" id="myModalLabel">请选择审批人</h4>
                                        </div>
                                        <div class="modal-body">
                                            <input type="text" id="search-username"/>
                                            <button type="button" class="btn btn-primary" id="search-approver">搜索
                                            </button>

                                            <div class="dropdown">
                                                <button id="approver-first" type="button"
                                                        class="btn btn-primary approver-data pull-left"
                                                        data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false" data-value="" style="width: 200px">
                                                    第一人
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu username-list" aria-labelledby="dLabel">
                                                </ul>
                                            </div>

                                            <div class="dropdown pull-left" style="margin-left: 20px">
                                                <button id="approver-second" type="button"
                                                        class="btn btn-primary approver-data " data-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false" data-value=""
                                                        style="width: 200px">
                                                    第二人
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu username-list" aria-labelledby="dLabel">
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="modal-footer" style="margin-top: 25px">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">关闭
                                            </button>
                                            <button type="button" class="btn btn-primary" id="confirm-approver">确定
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @foreach($dataTypeRows as $row)
                                @if($row->field=='approver')
                                    <div class="form-group @if($row->type == 'hidden') hidden @endif">
                                        <label for="name">{{ $row->display_name }}</label>
                                        @include('voyager::multilingual.input-hidden-bread-edit-add')
                                        <input type="text" name="approver" hidden/>

                                        <input type="text" id="approver_name" class="form-control" readonly="readonly" @if(isset($dataTypeContent->id)) value="{{$approver_str}}" @endif/>
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-primary btn-lg" id="approver-type">
                                            请选择审核人
                                        </button>
                                        @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                            {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                        @endforeach
                                    </div>
                                @elseif($row->field=='relevant')
                                    <div class="form-group @if($row->type == 'hidden') hidden @endif">
                                        <label for="name">{{ $row->display_name }}</label>
                                        @include('voyager::multilingual.input-hidden-bread-edit-add')
                                        <input type="text" name="relevant" hidden/>

                                        <input type="text" id="relevant_name" class="form-control" readonly="readonly" @if(isset($dataTypeContent->id)) value="{{$relevant_str}}" @endif/>

                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-primary btn-lg" id="relevant-type">
                                            请选择相关人
                                        </button>
                                        @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                            {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                        @endforeach
                                    </div>
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

    <div class="modal fade modal-danger" id="confirm_delete_modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="voyager-warning"></i>你确定?</h4>
                </div>

                <div class="modal-body">
                    <h4>你确定想删除 '<span class="confirm_delete_name"></span>'</h4>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-danger" id="confirm_delete">确认,删除
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete File Modal -->
@stop

@section('javascript')
    <script>
        var params = {}
        var $image

        $('document').ready(function () {
            $('.toggleswitch').bootstrapToggle();

            //Init datepicker for date fields if data-datepicker attribute defined
            //or if browser does not handle date inputs
            $('.form-group input[type=date]').each(function (idx, elt) {
                if (elt.type != 'date' || elt.hasAttribute('data-datepicker')) {
                    elt.type = 'text';
                    $(elt).datetimepicker($(elt).data('datepicker'));
                }
            });

            @if ($isModelTranslatable)
                $('.side-body').multilingual({"editing": true});
            @endif

            $('.side-body input[data-slug-origin]').each(function (i, el) {
                $(el).slugify();
            });

            //初始化用户名选择列表
            function initUsernameList() {
                $.post('/admin/getUsersNameList', function (data) {
                    $(data.data).each(function (key, value) {
                        var job_number = value.job_number;
                        var username = value.username;
                        var part_name = value.part_name;

                        $(".username-list").append("<li class='btn-default' value=' " + job_number + "'>" + username + "---" + part_name + "</li>");
                    })
                })
            }

            initUsernameList();

            $('.form-group').on('click', '.remove-multi-image', function (e) {
                $image = $(this).siblings('img');

                params = {
                    slug: '{{ $dataTypeContent->getTable() }}',
                    image: $image.data('image'),
                    id: $image.data('id'),
                    field: $image.parent().data('field-name'),
                    _token: '{{ csrf_token() }}'
                }

                $('.confirm_delete_name').text($image.data('image'));
                $('#confirm_delete_modal').modal('show');
            });

            $('#confirm_delete').on('click', function () {
                $.post('{{ route('voyager.media.remove') }}', params, function (response) {
                    if (response
                            && response.data
                            && response.data.status
                            && response.data.status == 200) {

                        toastr.success(response.data.message);
                        $image.parent().fadeOut(300, function () {
                            $(this).remove();
                        })
                    } else {
                        toastr.error("Error removing image.");
                    }
                });

                $('#confirm_delete_modal').modal('hide');
            });
            $('[data-toggle="tooltip"]').tooltip();

            $("#approver-type").click(function () {
                $("#myModal").modal('show')
                $("#myModal").data('type', 'approver')
            })

            $("#relevant-type").click(function () {
                $("#myModal").modal('show')
                $("#myModal").data('type', 'relevant')
            })

            /**
             * 点击搜索人员
             */
            $("#search-approver").on('click', function (e) {
                var username = $("#search-username").val();
                $.post('/admin/getUsersNameList', {"username": username}, function (data) {
                    //清空节点
                    $(".dropdown-menu li").remove();
                    $(data.data).each(function (key, value) {
                        var job_number = value.job_number;
                        var username = value.username;
                        var part_name = value.part_name;

                        $(".username-list").append("<li class='btn-default' value=' " + job_number + "'>" + username + "---" + part_name + "</li>");
                    })
                })
            })

            /**
             * 确认选择
             */
            $("#confirm-approver").on('click', function () {
                if ($("#myModal").data('type') === 'approver') {
                    var approver_first_value = $("#approver-first").data('value');
                    var approver_second_value = $("#approver-second").data('value');

                    var approver_first_name = $("#approver-first").data('name');
                    var approver_second_name = $("#approver-second").data('name');


                    var approver_value = approver_first_value + "," + approver_second_value;
                    var approver_name = approver_first_name + "," + approver_second_name;

                    $("input[name='approver']").val(approver_value)
                    $("#approver_name").val(approver_name)
                } else {
                    var relevant_first_value = $("#approver-first").data('value');
                    var relevant_second_value = $("#approver-second").data('value');

                    var relevant_first_name = $("#approver-first").data('name');
                    var relevant_second_name = $("#approver-second").data('name');


                    var relevant_value = relevant_first_value + "," + relevant_second_value;
                    var relevant_name = relevant_first_name + "," + relevant_second_name;

                    $("input[name='relevant']").val(relevant_value)
                    $("#relevant_name").val(relevant_name)
                }

                $("#myModal").modal('hide')
            })

            //下拉选择事件
            $(".dropdown-menu").bind('click', function (e) {
                var name = $(e.target).text();
                var job_number = $(e.target).val();
                $(e.target).parent().prev().text(name)
                $(e.target).parent().prev().data('value', job_number)
                $(e.target).parent().prev().data('name', name)
            })
        });
    </script>
    @if($isModelTranslatable)
        <script src="{{ voyager_asset('js/multilingual.js') }}"></script>
    @endif
    <script src="{{ voyager_asset('lib/js/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ voyager_asset('js/voyager_tinymce.js') }}"></script>
    <script src="{{ voyager_asset('lib/js/ace/ace.js') }}"></script>
    <script src="{{ voyager_asset('js/voyager_ace_editor.js') }}"></script>
    <script src="{{ voyager_asset('js/slugify.js') }}"></script>
@stop
