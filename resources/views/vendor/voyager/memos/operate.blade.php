@if (Voyager::can('delete_attendances'))
    <a href="javascript:;" title="Delete" class="btn btn-sm btn-danger pull-right delete" data-id="{{ $memo->id }}"
       id="delete-{{ $memo->id }}">
        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">删除</span>
    </a>
@endif
@if (Voyager::can('edit_attendances'))
    <a href="{{ route('voyager.attendances.edit', $memo->id) }}" title="Edit"
       class="btn btn-sm btn-primary pull-right edit">
        <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">修改</span>
    </a>
@endif
@if (Voyager::can('read_attendances'))
    <a href="{{ route('voyager.attendances.show', $memo->id) }}" title="View"
       class="btn btn-sm btn-warning pull-right">
        <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">查看</span>
    </a>
@endif

