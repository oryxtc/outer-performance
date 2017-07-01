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
            <i class="voyager-double-down"></i> 导入社保和公积金
        </button>
        <button type="button" class="btn btn-success" id="exportUsers">
            <i class="voyager-double-up"></i> 导出社保和公积金
        </button>
        <a href="{{ route('excel.exportUsers',['checkData'=>'*']) }}" class="btn btn-success">
            <i class="voyager-plus"></i> 导出所有社保和公积金
        </a>

        <form hidden method="post" action="/exportUsers" id="search-form" target="_blank">

        </form>

        <a href="{{ route('excel.exportProvidentsTemplate') }}" class="btn btn-success pull-right">
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
        <form method="post" id="search-form" class="form-inline" role="form">
            <div class="dropdown" style="margin-left: 4%">
                <button id="dLabel" type="button" class="btn btn-info" data-toggle="dropdown" data-value=""
                        data-name=""
                        aria-haspopup="true"
                        aria-expanded="false" style="width: 124px">
                    请选择字段
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dLabel">
                    @foreach($providentData as $key=>$value)
                        <li class='btn-default' data-value="{{$key}}">{{$value}}</li>
                    @endforeach
                </ul>
                <input type="text" id="search-data" class="form-control" data-name="">
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
                                <th>姓名</th>
                                <th>邮箱</th>
                                <th>所属期间</th>
                                <th>社保个人部分</th>
                                <th>社保公司部分</th>
                                <th>公积金个人部分</th>
                                <th>公积金公司部分</th>
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


            //初始化datatables
            var oTable = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{!! route('getProvidentsList') !!}',
                    data: function (d) {
                        var name = $("#search-data").data('name');
                        d[name] = $("#search-data").val();
                    }
                },
                columns: [
                    {data: 'username', name: 'username'},
                    {data: 'email', name: 'email'},
                    {data: 'period_at', name: 'period_at'},
                    {data: 'social_security_personal', name: 'social_security_personal'},
                    {data: 'social_security_company', name: 'social_security_company'},
                    {data: 'provident_fund_personal', name: 'provident_fund_personal'},
                    {data: 'provident_fund_company', name: 'provident_fund_company'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action'}
                ]
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
                oTable.draw();
                e.preventDefault();
            });

            //上传员工表
            $("#upload").click(function () {
                $.ajax({
                    url: '/importUsers',
                    type: 'POST',
                    cache: false,
                    data: new FormData($('#uploadForm')[0]),
                    processData: false,
                    contentType: false
                }).done(function (res) {
                    if (res.status === true) {
                        $(".alert-success").show().delay(3000).hide(0)
                    } else {
                        $(".alert-danger").html(res.message).delay(5000).show()
                    }
                }).fail(function (res) {
                    $(".alert-danger").text('导入失败!').show().delay(3000).hide(0)
                });
            })

            //导出员工信息列表
            $('#exportUsers').click(function () {
                var data = {}
                var check_data_list = $(".ckeck-data input:checked")
                //必须至少选择一个
                if ($(check_data_list).length == 0) {
                    $(".alert-danger").text('请至少选择一个字段进行导出!').show().delay(3000).hide(0)
                    return
                }
                //选择的
                $(check_data_list).each(function (key, value) {
                    $('#search-form').append("<input type='text' name=checkData[" + key + "] value=" + $(value).val() + " />")
                })
                //搜索栏
                if ($("#dLabel").data('value')) {
                    var search_key = $("#dLabel").data('name');
                    var search_value = $("#dLabel").data('value');
                    $('#search-form').append("<input type='text' name=searchData[" + search_key + "] value=" + search_value + " >")
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
