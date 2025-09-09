@extends('back.layouts.base-admin')
@section('content')
    <form method="post" action="{{ $manufacturer->exists ? route('catalog.manufacturers.update', $manufacturer) : route('catalog.manufacturers.store') }}">
        @csrf
        @if($manufacturer->exists) @method('PATCH') @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">@lang('back/manufacturers.basic_info')</h6></div>
                    <div class="card-body">

                        <ul class="nav nav-tabs" role="tablist">
                            @php($locales = config('app.locales'))
                            @foreach($locales as $code => $label)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link @if($loop->first) active @endif" data-bs-toggle="tab" data-bs-target="#tab-{{ $code }}" type="button" role="tab">{{ $label }}</button>
                                </li>
                            @endforeach
                        </ul>
                        <div class="tab-content border-start border-end border-bottom p-3">
                            @foreach($locales as $code => $label)
                                <div class="tab-pane fade @if($loop->first) show active @endif" id="tab-{{ $code }}" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label" for="title-{{ $code }}">@lang('back/manufacturers.title_label')</label>
                                        <input id="title-{{ $code }}" type="text" name="title[{{ $code }}]" class="form-control" value="{{ old('title.'.$code, optional($manufacturer->translation($code))->title) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="slug-{{ $code }}">@lang('back/manufacturers.slug')</label>
                                        <input id="slug-{{ $code }}" type="text" name="slug[{{ $code }}]" class="form-control" value="{{ old('slug.'.$code, optional($manufacturer->translation($code))->slug) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="description-{{ $code }}">@lang('back/manufacturers.description')</label>
                                        <textarea id="description-{{ $code }}" name="description[{{ $code }}]" class="form-control" rows="4">{{ old('description.'.$code, optional($manufacturer->translation($code))->description) }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-sm-6">
                                <label class="form-label">@lang('back/manufacturers.website')</label>
                                <input name="website_url" class="form-control" value="{{ old('website_url', $manufacturer->website_url) }}">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">@lang('back/manufacturers.support_email')</label>
                                <input name="support_email" class="form-control" value="{{ old('support_email', $manufacturer->support_email) }}">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">@lang('back/manufacturers.phone')</label>
                                <input name="phone" class="form-control" value="{{ old('phone', $manufacturer->phone) }}">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">@lang('back/manufacturers.country')</label>
                                <input name="country_code" class="form-control" value="{{ old('country_code', $manufacturer->country_code) }}" maxlength="2">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">@lang('back/manufacturers.established_year')</label>
                                <input name="established_year" type="number" min="1800" max="{{ now()->year }}" class="form-control" value="{{ old('established_year', $manufacturer->established_year) }}">
                            </div>
                            <div class="col-sm-8">
                                <label class="form-label">@lang('back/manufacturers.logo_path')</label>
                                <input name="logo_path" class="form-control" value="{{ old('logo_path', $manufacturer->logo_path) }}" placeholder="media/brands/acme.png">
                            </div>
                            <div class="col-sm-4 d-flex align-items-end gap-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="featured" value="1" @checked(old('featured', $manufacturer->featured))>
                                    <label class="form-check-label">@lang('back/manufacturers.featured')</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="status" value="1" @checked(old('status', $manufacturer->status))>
                                    <label class="form-check-label">@lang('back/manufacturers.active')</label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Assigned products --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">@lang('back/manufacturers.assigned_products')</h6>
                        {{-- Optional: jump to Products index filtered by this brand (see filter snippet below) --}}
                        {{-- <a href="{{ route('admin.products.index', ['manufacturer' => $manufacturer->id]) }}" class="btn btn-sm btn-outline-secondary">@lang('back/manufacturers.view_all')</a> --}}
                    </div>
                    <div class="card-body">
                        {{-- client-side quick filter (not submitted with the form) --}}
                        <input type="search" class="form-control mb-2" placeholder="@lang('back/manufacturers.search_products_placeholder')" oninput="(function(v){const rows=document.querySelectorAll('#brand-products tbody tr');rows.forEach(r=>{r.style.display=r.textContent.toLowerCase().includes(v.toLowerCase())?'':'none';});})(this.value)">

                        <div class="table-responsive">
                            <table class="table table-sm align-middle" id="brand-products">
                                <thead>
                                <tr>
                                    <th style="width:70px">ID</th>
                                    <th>@lang('back/products.product')</th>
                                    <th>SKU</th>
                                    <th class="text-center">@lang('back/products.status')</th>
                                    <th class="text-end">@lang('back/products.actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($products as $p)
                                    <tr>
                                        <td>{{ $p->id }}</td>
                                        <td>{{ optional($p->translation())->title ?? '—' }}</td>
                                        <td>{{ $p->sku }}</td>
                                        <td class="text-center">
                <span class="badge {{ $p->status ? 'bg-success' : 'bg-secondary' }}">
                  {{ $p->status ? __('back/products.active') : __('back/products.inactive') }}
                </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-xs btn-outline-primary">
                                                @lang('back/products.edit')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            @lang('back/manufacturers.no_assigned_products')
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination works fine inside the POST form since links are anchors --}}
                        {{ $products->links() }}
                    </div>
                </div>

            </div>
        </div>
    </form>
@endsection
