@php
    $avatar = $detail->avatar ? asset($detail->avatar) : asset('media/avatars/default_avatar.png');
@endphp
<tr>
    <td>{{ $detail->id }}</td>
    <td>
        <div class="d-flex align-items-center gap-2">
            <img src="{{ $avatar }}" class="rounded-circle border" style="width:36px;height:36px;object-fit:cover;">
            <div>
                <div class="fw-semibold">{{ $detail->full_name }}</div>
                <div class="text-muted small text-uppercase">{{ __('back/users.tabs.'.$detail->role) }}</div>
            </div>
        </div>
    </td>
    <td>{{ $detail->user?->email }}</td>
    <td>{{ $detail->phone ?? '' }}</td>
    <td>{{ $detail->city ?? '' }}</td>
    <td>
        @if($detail->status)
            <span class="badge bg-success">{{ __('back/common.status.active') }}</span>
        @else
            <span class="badge bg-danger-subtle">{{ __('back/common.status.hidden') }}</span>
        @endif
    </td>
    <td class="text-muted">{{ $detail->updated_at?->format('Y-m-d H:i') }}</td>
    <td class="text-end">
        <div class="d-inline-flex gap-2">
            @can('update', $detail)
                <a href="{{ route('users.edit', $detail) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="@lang('back/common.actions.edit')">
                    <i class="ti ti-edit"></i>
                </a>
            @endcan
            @can('delete', $detail)
                <form action="{{ route('users.destroy', $detail) }}" method="POST"
                      onsubmit="return confirm('{{ __('back/users.confirm_delete') }}')" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger rounded-circle" title="@lang('back/common.actions.delete')">
                        <i class="ti ti-trash"></i>
                    </button>
                </form>
            @endcan
        </div>
    </td>
</tr>
