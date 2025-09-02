@extends('back.layouts.base-admin')

@section('title', __('back/categories.title'))

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header align-items-center justify-content-between d-flex">
                    <div>
                        <h5 class="mb-1">{{ __('back/categories.title') }}</h5>
                        @if($group)
                            <div class="small text-muted">
                                {{ __('back/categories.group_label') }}:
                                <span class="text-uppercase">{{ __('back/categories.tabs.'.($group ?? 'products')) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('catalog.categories.create', ['group'=>$group]) }}" class="btn btn-primary">
                            <i class="ti ti-plus"></i> {{ __('back/common.actions.new') }}
                        </a>
                    </div>
                </div>

                {{-- group filter --}}
                <div class="card-body border-bottom pb-0">
                    @php $groups = ['products','blog','pages','footer']; @endphp
                    <ul class="nav nav-pills flex-wrap">
                        @foreach($groups as $key)
                            <li class="nav-item me-2 mb-2">
                                <a class="nav-link {{ ($group ?? 'products') === $key ? 'active' : '' }}"
                                   href="{{ route('catalog.categories.index', ['group'=>$key]) }}">
                                    {{ __('back/categories.tabs.'.$key) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if(session('success'))
                    <div class="alert alert-success m-3 mb-0">{{ session('success') }}</div>
                @endif

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th style="width:56px;">{{ __('back/categories.table.id') }}</th>
                                <th>{{ __('back/categories.table.name') }}</th>
                                <th style="width:140px;">{{ __('back/categories.table.group') }}</th>
                                <th>{{ __('back/categories.table.parent') }}</th>
                                <th style="width:90px;">{{ __('back/categories.table.sort') }}</th>
                                <th style="width:110px;">{{ __('back/categories.table.status') }}</th>
                                <th style="width:150px;">{{ __('back/categories.table.updated') }}</th>
                                <th class="text-end" style="width:120px;">{{ __('back/categories.table.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($categories as $cat)
                                <tr>
                                    <td>{{ $cat->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($cat->image)
                                                <img src="{{ $cat->image }}" alt="" class="rounded" style="width:28px;height:28px;object-fit:cover;">
                                            @else
                                                <span class="avatar bg-light border rounded" style="width:28px;height:28px;"><i class="ph-duotone ph-image text-muted"></i></span>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ $cat->name }}</div>
                                                @if($cat->slug)
                                                    <div class="text-muted small">{{ $cat->slug }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-secondary text-uppercase">{{ $cat->group }}</span></td>
                                    <td>{{ optional($cat->parent)->name ?: __('back/common.none') }}</td>
                                    <td>{{ $cat->sort_order }}</td>
                                    <td>
                                        @if($cat->is_active)
                                            <span class="badge bg-success">{{ __('back/common.status.active') }}</span>
                                        @else
                                            <span class="badge bg-outline-secondary">{{ __('back/common.status.hidden') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $cat->updated_at?->format('Y-m-d H:i') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('catalog.categories.edit', [$cat, 'group'=>$group]) }}"
                                               class="btn btn-sm btn-outline-primary" title="{{ __('back/common.actions.edit') }}">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <form action="{{ route('catalog.categories.destroy', $cat) }}" method="POST"
                                                  onsubmit="return confirm('{{ __('back/categories.confirm_delete') }}')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="{{ __('back/common.actions.delete') }}">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                @foreach($cat->children as $child)
                                    <tr>
                                        <td>{{ $child->id }}</td>
                                        <td class="ps-5">— {{ $child->name }}</td>
                                        <td><span class="badge bg-secondary text-uppercase">{{ $child->group }}</span></td>
                                        <td>{{ optional($child->parent)->name ?: __('back/common.none') }}</td>
                                        <td>{{ $child->sort_order }}</td>
                                        <td>
                                            @if($child->is_active)
                                                <span class="badge bg-success">{{ __('back/common.status.active') }}</span>
                                            @else
                                                <span class="badge bg-outline-secondary">{{ __('back/common.status.hidden') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ $child->updated_at?->format('Y-m-d H:i') }}</td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="{{ route('catalog.categories.edit', [$child, 'group'=>$group]) }}"
                                                   class="btn btn-sm btn-outline-primary" title="{{ __('back/common.actions.edit') }}">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <form action="{{ route('catalog.categories.destroy', $child) }}" method="POST"
                                                      onsubmit="return confirm('{{ __('back/categories.confirm_delete') }}')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" title="{{ __('back/common.actions.delete') }}">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">{{ __('back/categories.empty') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(method_exists($categories, 'links'))
                    <div class="card-footer">
                        {{ $categories->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
