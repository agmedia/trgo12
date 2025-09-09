@extends('back.layouts.base-admin')
@section('content')
    <form method="post" action="{{ $product->exists ? route('catalog.products.update', $product) : route('catalog.products.store') }}">
        @csrf
        @if($product->exists) @method('PATCH') @endif


        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">@lang('back/products.basic_info')</h6></div>
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
                                        <label class="form-label" for="title-{{ $code }}">@lang('back/products.title_label')</label>
                                        <input id="title-{{ $code }}" type="text" name="title[{{ $code }}]" class="form-control" value="{{ old('title.'.$code, optional($product->translation($code))->title) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="slug-{{ $code }}">@lang('back/products.slug')</label>
                                        <input id="slug-{{ $code }}" type="text" name="slug[{{ $code }}]" class="form-control" value="{{ old('slug.'.$code, optional($product->translation($code))->slug) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="description-{{ $code }}">@lang('back/products.description')</label>
                                        <textarea id="description-{{ $code }}" name="description[{{ $code }}]" class="form-control" rows="4">{{ old('description.'.$code, optional($product->translation($code))->description) }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>


                        <div class="row g-3 mt-2">
                            <div class="col-sm-4">
                                <label class="form-label">SKU</label>
                                <input name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">@lang('back/products.price')</label>
                                <input name="price" type="number" step="0.01" class="form-control" value="{{ old('price', $product->price) }}" required>
                            </div>
                            <div class="col-sm-4 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="status" value="1" @checked(old('status', $product->status))>
                                    <label class="form-check-label">@lang('back/products.active')</label>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>


            <div class="col-lg-4">
                {{-- Brand / Manufacturer --}}
                <div class="card mb-3">
                    <div class="card-header"><h6 class="mb-0">@lang('back/products.manufacturer')</h6></div>
                    <div class="card-body">
                        <select class="form-select" name="manufacturer_id">
                            <option value="">{{ __('back/products.no_manufacturer') }}</option>
                            @foreach($manufacturers as $id => $name)
                                <option value="{{ $id }}" @selected(old('manufacturer_id', $product->manufacturer_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h6 class="mb-0">@lang('back/products.categories')</h6></div>
                    <div class="card-body">
                        <select class="form-select" name="categories[]" id="category-select" multiple required>
                            @foreach($categories as $id => $title)
                                <option value="{{ $id }}" @selected(in_array($id, old('categories', $product->categories->pluck('id')->all())))>{{ $title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-primary" type="submit">{{ $product->exists ? __('back/products.save_changes') : __('back/products.create') }}</button>
                    <a href="{{ route('catalog.products.index') }}" class="btn btn-outline-secondary">@lang('back/products.cancel')</a>
                </div>
            </div>
        </div>
    </form>
@endsection