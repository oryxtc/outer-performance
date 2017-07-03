@if (Voyager::can('delete_users'))
    <a href="javascript:;" title="Delete" class="btn btn-sm btn-danger pull-right delete" data-id="{{ $wage->id }}"
       id="delete-{{ $wage->id }}">
        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">删除</span>
    </a>
@endif
@if (Voyager::can('edit_users'))
    <a href="{{ route('voyager.users.edit', $wage->id) }}" title="Edit"
       class="btn btn-sm btn-primary pull-right edit">
        <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">修改</span>
    </a>
@endif
@if (Voyager::can('read_users'))
    <a href="{{ route('voyager.users.show', $wage->id) }}" title="View"
       class="btn btn-sm btn-warning pull-right">
        <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">查看</span>
    </a>
@endif

