@extends('voyager::master')

@section('page_header')
    <h1 class="page-title" style="width: 96%">
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
    <h3 class="page-title">
        <i class="voyager-search"></i> 勾选显示字段
        <div>
            @foreach($checkData as $key=>$value)
                <label>
                    <input type="checkbox" name="checkData" value="{{$key}}">

                    {{$value}}&nbsp;&nbsp;
                </label>
            @endforeach
        </div>
    </h3>
@stop

@section('content')
    <div class="page-content container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <table id="dataTable" class="table table-hover">
                            <thead>
                            <tr>
                                @foreach($dataType->browseRows as $rows)
                                    <th class="{{$rows->field}}" hidden>{{ $rows->display_name }}</th>
                                @endforeach
                                <th class="actions text-center">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($dataTypeContent as $data)
                                <tr>
                                    @foreach($dataType->browseRows as $row)
                                        <td class="{{$row->field}}" hidden>
                                            <?php $options = json_decode($row->details); ?>
                                            @if($row->type == 'image')
                                                <img src="@if( strpos($data->{$row->field}, 'http://') === false && strpos($data->{$row->field}, 'https://') === false){{ Voyager::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif"
                                                     style="width:100px">
                                            @elseif($row->type == 'select_multiple')
                                                @if(property_exists($options, 'relationship'))

                                                    @foreach($data->{$row->field} as $item)
                                                        @if($item->{$row->field . '_page_slug'})
                                                            <a href="{{ $item->{$row->field . '_page_slug'} }}">{{ $item->{$row->field} }}</a>@if(!$loop->last)
                                                                , @endif
                                                        @else
                                                            {{ $item->{$row->field} }}
                                                        @endif
                                                    @endforeach

                                                    {{-- $data->{$row->field}->implode($options->relationship->label, ', ') --}}
                                                @elseif(property_exists($options, 'options'))
                                                    @foreach($data->{$row->field} as $item)
                                                        {{ $options->options->{$item} . (!$loop->last ? ', ' : '') }}
                                                    @endforeach
                                                @endif

                                            @elseif($row->type == 'select_dropdown' && property_exists($options, 'options'))

                                                @if($data->{$row->field . '_page_slug'})
                                                    <a href="{{ $data->{$row->field . '_page_slug'} }}">{!! $options->options->{$data->{$row->field}} !!}</a>
                                                @else
                                                    {!! $options->options->{$data->{$row->field}} !!}
                                                @endif


                                            @elseif($row->type == 'select_dropdown' && $data->{$row->field . '_page_slug'})
                                                <a href="{{ $data->{$row->field . '_page_slug'} }}">{{ $data->{$row->field} }}</a>
                                            @elseif($row->type == 'date')
                                                {{ $options && property_exists($options, 'format') ? \Carbon\Carbon::parse($data->{$row->field})->formatLocalized($options->format) : $data->{$row->field} }}
                                            @elseif($row->type == 'checkbox')
                                                @if($options && property_exists($options, 'on') && property_exists($options, 'off'))
                                                    @if($data->{$row->field})
                                                        <span class="label label-info">{{ $options->on }}</span>
                                                    @else
                                                        <span class="label label-primary">{{ $options->off }}</span>
                                                    @endif
                                                @else
                                                    {{ $data->{$row->field} }}
                                                @endif
                                            @elseif($row->type == 'text')
                                                @include('voyager::multilingual.input-hidden-bread-browse')
                                                <div class="readmore">{{ strlen( $data->{$row->field} ) > 200 ? substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>
                                            @elseif($row->type == 'text_area')
                                                @include('voyager::multilingual.input-hidden-bread-browse')
                                                <div class="readmore">{{ strlen( $data->{$row->field} ) > 200 ? substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>
                                            @elseif($row->type == 'file' && !empty($data->{$row->field}) )
                                                @include('voyager::multilingual.input-hidden-bread-browse')
                                                <a href="/storage/{{ $data->{$row->field} }}">Download</a>
                                            @elseif($row->type == 'rich_text_box')
                                                @include('voyager::multilingual.input-hidden-bread-browse')
                                                <div class="readmore">{{ strlen( strip_tags($data->{$row->field}, '<b><i><u>') ) > 200 ? substr(strip_tags($data->{$row->field}, '<b><i><u>'), 0, 200) . ' ...' : strip_tags($data->{$row->field}, '<b><i><u>') }}</div>
                                            @else
                                                @include('voyager::multilingual.input-hidden-bread-browse')
                                                <span>{{ $data->{$row->field} }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="no-sort no-click" id="bread-actions">
                                        @if (Voyager::can('delete_'.$dataType->name))
                                            <a href="javascript:;" title="Delete"
                                               class="btn btn-sm btn-danger pull-right delete" data-id="{{ $data->id }}"
                                               id="delete-{{ $data->id }}">
                                                <i class="voyager-trash"></i> <span
                                                        class="hidden-xs hidden-sm">删除</span>
                                            </a>
                                        @endif
                                        @if (Voyager::can('edit_'.$dataType->name))
                                            <a href="{{ route('voyager.'.$dataType->slug.'.edit', $data->id) }}"
                                               title="Edit" class="btn btn-sm btn-primary pull-right edit">
                                                <i class="voyager-edit"></i> <span
                                                        class="hidden-xs hidden-sm">修改</span>
                                            </a>
                                        @endif
                                        @if (Voyager::can('read_'.$dataType->name))
                                            <a href="{{ route('voyager.'.$dataType->slug.'.show', $data->id) }}"
                                               title="View" class="btn btn-sm btn-warning pull-right">
                                                <i class="voyager-eye"></i> <span
                                                        class="hidden-xs hidden-sm">查看</span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if (isset($dataType->server_side) && $dataType->server_side)
                            <div class="pull-left">
                                <div role="status" class="show-res" aria-live="polite">
                                    显示 {{ $dataTypeContent->firstItem() }} 至 {{ $dataTypeContent->lastItem() }}
                                    条,共 {{ $dataTypeContent->total() }} 条
                                </div>
                            </div>
                            <div class="pull-right">
                                {{ $dataTypeContent->links() }}
                            </div>
                        @endif
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
        @if (!$dataType->server_side)
            $(document).ready(function () {
            $('#dataTable').DataTable({"order": []});
        });
        @endif

        //初始化勾选
        $("input[name='checkData'][value='belong_company']").attr('checked', true)
        $("input[name='checkData'][value='username']").attr('checked', true)

        //初始化勾选栏
        var checkData = $("input[name='checkData']");
        $.each(checkData, function (key, item) {
            var ele = "." + $(item).val();
            if ($(item).prop('checked')) {
                $(ele).show();
            }
        })

        //时间绑定
        $(checkData).on('click', function (e) {
            var ele = "." + $(e.target).val();
            if ($(e.target).prop('checked')) {
                $(ele).show();
            } else {
                $(ele).hide();
            }
            console.log($(e.target).val())
        })

        $('td').on('click', '.delete', function (e) {
            var form = $('#delete_form')[0];

            form.action = parseActionUrl(form.action, $(this).data('id'));

            $('#delete_modal').modal('show');
        });

        function parseActionUrl(action, id) {
            return action.match(/\/[0-9]+$/)
                    ? action.replace(/([0-9]+$)/, id)
                    : action + '/' + id;
        }

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
//            var username=$("input[name='username']").val();
            $.post('/exportUsers', function (data) {
                console.log(data)
            })
        })
    </script>
@stop
