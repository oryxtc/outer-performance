@if($wage->status==0)
    @if (Voyager::can('edit_wages'))
        <a href="{{ route('voyager.wages.edit', $wage->id) }}" title="Edit"
           class="btn btn-sm btn-primary pull-right edit">
            <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">修改</span>
        </a>
    @endif
@endif
@if (Voyager::can('delete_wages'))
    <a href="javascript:;" title="Delete" class="btn btn-sm btn-danger pull-right delete" data-id="{{ $wage->id }}"
       id="delete-{{ $wage->id }}">
        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">删除</span>
    </a>
@endif
@if (Voyager::can('read_wages'))
    <a href="{{ route('voyager.wages.show', $wage->id) }}" title="View"
       class="btn btn-sm btn-warning pull-right">
        <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">查看</span>
    </a>
@endif

