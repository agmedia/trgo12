@extends('back.layouts.base-admin')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">@lang('back/manufacturers.title')</h5>
                <a href="{{ route('catalog.manufacturers.create') }}" class="btn btn-primary">@lang('back/manufacturers.add')</a>
            </div>
            <div class="card-body">
                <form method="get" class="row g-2 mb-3">
                    <div class="col-sm-6"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="@lang('back/manufacturers.search')"></div>
                    <div class="col-sm-3">
                        <select name="status" class="form-select">
                            <option value="">@lang('back/manufacturers.all_statuses')</option>
                            <option value="1" @selected(request('status')==='1')>@lang('back/manufacturers.active')</option>
                            <option value="0" @selected(request('status')==='0')>@lang('back/manufacturers.inactive')</option>
                        </select>
                    </div>
                    <div class="col-sm-3"><button class="btn btn-outline-secondary w-100">@lang('back/manufacturers.filter')</button></div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('back/manufacturers.name')</th>
                            <th class="text-center">@lang('back/manufacturers.status')</th>
                            <th class="text-center">@lang('back/manufacturers.products')</th>
                            <th class="text-end">@lang('back/manufacturers.actions')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($manufacturers as $m)
                            <tr>
                                <td>{{ $m->id }}</td>
                                <td>{{ optional($m->translation())->title ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $m->status ? 'bg-success' : 'bg-secondary' }}">{{ $m->status ? __('back/manufacturers.active') : __('back/manufacturers.inactive') }}</span>
                                </td>
                                <td class="text-center">{{ $m->products_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('catalog.manufacturers.edit', $m) }}" class="btn btn-sm btn-outline-primary">@lang('back/manufacturers.edit')</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">@lang('back/manufacturers.no_results')</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $manufacturers->links() }}
            </div>
        </div>
    </div>
@endsection
