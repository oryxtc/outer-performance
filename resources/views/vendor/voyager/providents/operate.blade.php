@if (Voyager::can('delete_users'))
    <a href="javascript:;" title="Delete" class="btn btn-sm btn-danger pull-right delete" data-id="{{ $provident->id }}"
       id="delete-{{ $provident->id }}">
        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">删除</span>
    </a>
@endif
@if (Voyager::can('edit_provindets'))
    <a href="{{ route('voyager.providents.edit', $provident->id) }}" title="Edit"
       class="btn btn-sm btn-primary pull-right edit">
        <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">修改</span>
    </a>
@endif
@if (Voyager::can('read_providents'))
    <a href="{{ route('voyager.providents.show', $provident->id) }}" title="View"
       class="btn btn-sm btn-warning pull-right">
        <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">查看</span>
    </a>
@endif

