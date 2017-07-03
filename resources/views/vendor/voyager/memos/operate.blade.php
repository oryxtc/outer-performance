@if (Voyager::can('delete_memos'))
    <a href="javascript:;" title="Delete" class="btn btn-sm btn-danger pull-right delete" data-id="{{ $memo->id }}"
       id="delete-{{ $memo->id }}">
        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">删除</span>
    </a>
@endif
@if (Voyager::can('edit_memos'))
    <a href="{{ route('voyager.memos.edit', $memo->id) }}" title="Edit"
       class="btn btn-sm btn-primary pull-right edit">
        <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">修改</span>
    </a>
@endif
@if (Voyager::can('read_memos'))
    <a href="{{ route('voyager.memos.show', $memo->id) }}" title="View"
       class="btn btn-sm btn-warning pull-right">
        <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">查看</span>
    </a>
@endif

