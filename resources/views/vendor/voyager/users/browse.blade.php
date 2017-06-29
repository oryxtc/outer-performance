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
            <i class="voyager-double-down"></i> 导入员工
        </button>
        <button type="button" class="btn btn-success" id="exportUsers">
            <i class="voyager-double-up"></i> 导出员工
        </button>
        <form hidden method="post" action="/exportUsers" id="search-form" target="_blank">

        </form>

        <a href="{{ route('excel.exportUsersTemplate') }}" class="btn btn-success pull-right">
            <i class="voyager-plus"></i> 导出模板
        </a>
        {{--导入员工模态框--}}
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">导入员工</h4>
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
    {{--多选控制--}}
    <h3 class="page-title" style="height: 50px">
        <i class="voyager-search"></i> 勾选显示字段
        <div class="ckeck-data">
            @foreach($checkData as $key=>$value)
                <label>
                    <input type="checkbox" name="checkData" value="{{$key}}" data-name="{{$value}}">

                    {{$value}}&nbsp;&nbsp;
                </label>
            @endforeach
        </div>
    </h3>
@stop

@section('content')
    <div class="page-content container-fluid">
        {{--下来选择框--}}
        <form method="post" id="search-form" class="form-inline" role="form">
            <div class="dropdown" style="margin-left: 4%">
                <button id="dLabel" type="button" class="btn btn-info" data-toggle="dropdown" data-value="" aria-haspopup="true"
                        aria-expanded="false" style="width: 116px">
                    请选择字段
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dLabel">

                </ul>
                <input type="text" id="search-data" class="form-control"  data-name="">
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
                                <th>Id</th>
                                <th>Username</th>
                                <th>Email</th>
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
                               value="确认删除 {{ $dataType->display_name_singular }}">
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
                    url:'{!! route('getUsersList') !!}',
                    data: function (d) {
                        var name=$("#search-data").data('name');
                        d[name] = $("#search-data").val();
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'username', name: 'username'},
                    {data: 'email', name: 'email'},
                ]
            });


            {{--//初始化勾选--}}
            $("input[name='checkData'][value='belong_company']").attr('checked', true);
            $("input[name='checkData'][value='username']").attr('checked', true);


            $('td').on('click', '.delete', function (e) {
                var form = $('#delete_form')[0];

                form.action = parseActionUrl(form.action, $(this).data('id'));

                $('#delete_modal').modal('show');
            });

            //下拉选择事件
            $(".dropdown-menu").bind('click', function (e) {
                var name = $(e.target).text()
                var value = $(e.target).data('value')
                $("#dLabel").text(name)
                $("#search-data").data('name',value)
//                $("#dLabel").data('value', value)
            })

            //点击搜索按钮
            $("#search-btn").click(function (e) {
                oTable.draw();
                e.preventDefault();
            });

            //点击选择按钮更新下来列表
            $("#dLabel").on('click', function (e) {
                var check_data_list = $(".ckeck-data input:checked")
                //清空节点
                $(".dropdown-menu li").remove();
                //选择的
                $(check_data_list).each(function (key, value) {
                    var html = "<li class='btn-default' data-value='" + $(value).val() + "'>" + $(value).data('name') + "</li>"
                    $(".dropdown-menu").append(html)
                })
            })

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
                        $(".alert-danger").html(res.message).show()
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
                if ($("#search-data").val()) {
                    var search_key = $("#dLabel").data('value')
                    $('#search-form').append("<input type='text' name=searchData[" + search_key + "] value=" + $("#search-data").val() + " >")
                }
                $("#search-form").submit()
            })
        });


        //初始化勾选栏
        //        var checkData = $("input[name='checkData']");
        //        $.each(checkData, function (key, item) {
        //            var ele = "." + $(item).val();
        //            if ($(item).prop('checked')) {
        //                $(ele).show();
        //            }
        //        })

        //时间绑定
        //        $(checkData).on('click', function (e) {
        //            var ele = "." + $(e.target).val();
        //            if ($(e.target).prop('checked')) {
        //                $(ele).show();
        //            } else {
        //                $(ele).hide();
        //            }
        //            console.log($(e.target).val())
        //        })



    </script>
@stop
