@extends('voyager::master')

@section('page_header')
    <h1 class="page-title" style="width: 96%;height: 50px">
        <i class="{{ $dataType->icon }}"></i> {{ $dataType->display_name_plural }}
        @if (Voyager::can('add_'.$dataType->name))
            <a href="{{ route('voyager.'.$dataType->slug.'.create') }}" class="btn btn-success">
                <i class="voyager-plus"></i> 新增
            </a>
        @endif

        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModal">
            <i class="voyager-double-down"></i> 导入考勤
        </button>
        <button type="button" class="btn btn-success" id="exportAttendances">
            <i class="voyager-double-up"></i> 导出考勤
        </button>
        <a href="{{ route('excel.exportAttendances',['checkData'=>'*']) }}" class="btn btn-success">
            <i class="voyager-plus"></i> 导出所有考勤
        </a>

        <form hidden method="post" action="/exportAttendances" id="search-form" target="_blank">

        </form>

        <a href="{{ route('excel.exportAttendancesTemplate') }}" class="btn btn-success pull-right">
            <i class="voyager-plus"></i> 导出模板
        </a>
        {{--导入员工模态框--}}
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">导入社保和公积金</h4>
                    </div>
                    {{--提示框--}}
                    <div class="alert alert-success" role="alert" hidden>导入成功!</div>
                    <div class="alert alert-danger" role="alert" hidden></div>

                    <form method="post" enctype="multipart/form-data" id="uploadForm">
                        <div class="modal-body">
                            <input id="file" type="file" name="file"/>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                            <button id="upload" type="button" class="btn btn-primary">确认</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </h1>
@stop
@section('content')
    <div class="page-content container-fluid">
        {{--下来选择框--}}
        <form method="post" id="" class="form-inline" role="form"
              style="margin-top: 20px;margin-left: -15px">
            <div class="dropdown" style="margin-left: 4%">
                <button id="dLabel" type="button" class="btn btn-info" data-toggle="dropdown" data-value=""
                        data-name=""
                        aria-haspopup="true"
                        aria-expanded="false" style="width: 124px">
                    请选择字段
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dLabel">
                    @foreach($attendanceData as $key=>$value)
                        <li class='btn-default' data-value="{{$key}}">{{$value}}</li>
                    @endforeach
                </ul>
                <input type="text" id="search-data" class="form-control" data-name="">


                <button  type="button" class="btn btn-info" data-value="" data-name=""  style="width: 110px;margin-left: 20px">
                    开始时间
                </button>
                <div class='input-group date form_datetime' id=''>
                    <input type='text' id="start-at" class="form-control" readonly="readonly"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar" ></span>
                    </span>
                </div>

                <button  type="button" class="btn btn-info" data-value=""data-name=""  style="width: 110px;margin-left: 20px">
                    结束时间
                </button>
                <div class='input-group date form_datetime' id=''>
                    <input type='text' id="end-at" class="form-control" readonly="readonly"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar" ></span>
                    </span>
                </div>

                <button type="submit" id="search-btn" class="btn btn-primary">搜索</button>
            </div>
        </form>
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <table id="users-table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>申请时间</th>
                                <th>姓名</th>
                                <th>类型</th>
                                <th>标题</th>
                                <th>事由</th>
                                <th>开始时间</th>
                                <th>结束时间</th>
                                <th>申请时长</th>
                                <th>状态</th>
                                <th class='text-center' style='width: 230px'>操作</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i>
                        你确定删除 {{ $dataType->display_name_singular }}?
                    </h4>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('voyager.'.$dataType->slug.'.index') }}" id="delete_form" method="POST">
                        {{ method_field("DELETE") }}
                        {{ csrf_field() }}
                        <input type="submit" class="btn btn-danger pull-right delete-confirm"
                               value="确认删除">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">取消</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop

@section('javascript')
    <!-- DataTables -->
    <script>

        $(function () {
            function parseActionUrl(action, id) {
                return action.match(/\/[0-9]+$/)
                        ? action.replace(/([0-9]+$)/, id)
                        : action + '/' + id;
            };
            //初始化日期控件
            $(".form_datetime").datetimepicker({
                locale: moment.locale('zh-cn'),
                viewMode: 'months',
                format: "YYYY-MM-DD HH:mm:SS",
                ignoreReadonly:true,
                showClear:true
            });

            //初始化datatables
            var oTable = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{!! route('getAttendancesList') !!}',
                    data: function (d) {
                        var name = $("#search-data").data('name');
                        d[name] = $("#search-data").val();
                        d['start_at'] = $("#start-at").val();
                        d['end_at'] = $("#end-at").val();
                    }
                },
                columns: [
                    {data: 'rownum', name: 'rownum'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'username', name: 'username'},
                    {data: 'type', name: 'type'},
                    {data: 'title', name: 'title'},
                    {data: 'reson', name: 'reson'},
                    {data: 'start_at', name: 'start_at'},
                    {data: 'end_at', name: 'end_at'},
                    {data: 'continued_at', name: 'continued_at'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action'}
                ],
            });

            //下拉选择事件
            $(".dropdown-menu").bind('click', function (e) {
                var name = $(e.target).text()
                var value = $(e.target).data('value')
                $("#dLabel").text(name)
                $("#search-data").data('name', value)
            })

            //点击搜索按钮
            $("#search-btn").click(function (e) {
                //赋值
                var search_key = $("#search-data").data('name');
                var search_value = $("#search-data").val();
                $("#dLabel").data('name', search_key);
                $("#dLabel").data('value', search_value);
                $("#start-at").data('value', $("#start-at").val());
                $("#end-at").data('value', $("#end-at").val());
                oTable.draw();
                e.preventDefault();
            });

            //上传员工表
            $("#upload").click(function () {
                $.ajax({
                    url: '/importAttendances',
                    type: 'POST',
                    cache: false,
                    data: new FormData($('#uploadForm')[0]),
                    processData: false,
                    contentType: false
                }).done(function (res) {
                    if (res.status === true) {
                        $(".alert-success").show().delay(3000).hide(0)
                    } else {
                        $(".alert-danger").html(res.message).show().delay(5000).hide(0)
                    }
                    setTimeout("window.location.reload()", 2000)
                }).fail(function (res) {
                    $(".alert-danger").text('导入失败!').show().delay(3000).hide(0)
                    setTimeout("window.location.reload()", 2000)
                });

            })

            //导出考勤
            $('#exportAttendances').click(function () {
                //先清空
                $("#search-form input").remove()
                //搜索栏
                if ($("#dLabel").data('value')) {
                    var search_key = $("#dLabel").data('name');
                    var search_value = $("#dLabel").data('value');
                    $('#search-form').append("<input type='text' name=searchData[" + search_key + "] value=" + search_value + " >")
                }
                if($("#start-at").data('value')){
                    $('#search-form').append("<input type='text' name='start_at' value=" + $("#start-at").data('value') + " >")
                }
                if($("#end-at").data('value')){
                    $('#search-form').append("<input type='text' name='end_at' value=" + $("#end-atd").data('value') + " >")
                }
                $("#search-form").submit()
            })
        });

        //删除按钮
        var deleteFormAction;
        $('table').on('click', '.delete', function (e) {
            var form = $('#delete_form')[0];

            if (!deleteFormAction) { // Save form action initial value
                deleteFormAction = form.action;
            }

            form.action = deleteFormAction.match(/\/[0-9]+$/)
                    ? deleteFormAction.replace(/([0-9]+$)/, $(this).data('id'))
                    : deleteFormAction + '/' + $(this).data('id');
            console.log(form.action);

            $('#delete_modal').modal('show');
        });
    </script>
@stop
