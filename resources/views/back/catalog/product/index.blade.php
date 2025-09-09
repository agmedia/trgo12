@extends('back.layouts.base-admin')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">@lang('back/products.title')</h5>
                <a href="{{ route('catalog.products.create') }}" class="btn btn-primary">@lang('back/products.add')</a>
            </div>
            <div class="card-body">
                <form method="get" class="row g-2 mb-3">
                    <div class="col-sm-6"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="@lang('back/products.search')"></div>
                    <div class="col-sm-3">
                        <select name="status" class="form-select">
                            <option value="">@lang('back/products.all_statuses')</option>
                            <option value="1" @selected(request('status')==='1')>@lang('back/products.active')</option>
                            <option value="0" @selected(request('status')==='0')>@lang('back/products.inactive')</option>
                        </select>
                    </div>
                    <div class="col-sm-3"><button class="btn btn-outline-secondary w-100">@lang('back/products.filter')</button></div>
                </form>


                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('back/products.product')</th>
                            <th class="text-center">@lang('back/products.status')</th>
                            <th class="text-end">@lang('back/products.actions')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($products as $p)
                            <tr>
                                <td>{{ $p->id }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="fw-semibold">{{ optional($p->translation())->title ?? '—' }}</div>
                                        <div class="text-muted small">SKU: {{ $p->sku }}</div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $p->status ? 'bg-success' : 'bg-secondary' }}">{{ $p->status ? __('back/products.active') : __('back/products.inactive') }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('catalog.products.edit', $p) }}" class="btn btn-sm btn-outline-primary">@lang('back/products.edit')</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">@lang('back/products.no_results')</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>


                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection