@extends('back.layouts.base-admin')
@section('content')
    <form method="post" action="{{ $option->exists ? route('catalog.options.update', $option) : route('catalog.options.store') }}">
        @csrf
        @if($option->exists) @method('PATCH') @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">Option</h6></div>
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
                                        <label class="form-label" for="title-{{ $code }}">Title</label>
                                        <input id="title-{{ $code }}" type="text" name="title[{{ $code }}]" class="form-control"
                                               value="{{ old('title.'.$code, optional($option->translation($code))->title) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="slug-{{ $code }}">Slug</label>
                                        <input id="slug-{{ $code }}" type="text" name="slug[{{ $code }}]" class="form-control"
                                               value="{{ old('slug.'.$code, optional($option->translation($code))->slug) }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-sm-6">
                                <label class="form-label">Sort order</label>
                                <input name="sort_order" type="number" min="0" class="form-control" value="{{ old('sort_order', $option->sort_order) }}">
                            </div>
                            <div class="col-sm-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="status" value="1" @checked(old('status', $option->status))>
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">Values</h6></div>
                    <div class="card-body">
                        {{-- dynamic repeater for values --}}
                        <div id="values-repeater">
                            @php($rows = old('values', $values?->map(fn($v) => [
                              'id' => $v->id,
                              'status' => $v->status,
                              'sort_order' => $v->sort_order,
                              'title' => collect($locales)->mapWithKeys(fn($l,$code)=>[$code => optional($v->translation($code))->title])->toArray() ?? [],
                            ])->toArray() ?? []))
                            @forelse($rows as $i => $row)
                                <div class="border rounded p-3 mb-3">
                                    <input type="hidden" name="values[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong>Value #{{ $i+1 }}</strong>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.border').remove()">Remove</button>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6"><label class="form-label">Sort</label><input class="form-control" type="number" name="values[{{ $i }}][sort_order]" value="{{ $row['sort_order'] ?? 0 }}"></div>
                                        <div class="col-6 d-flex align-items-end">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="values[{{ $i }}][status]" value="1" @checked(($row['status'] ?? true))>
                                                <label class="form-check-label">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach($locales as $code => $label)
                                        <div class="mb-2">
                                            <label class="form-label">{{ $label }}</label>
                                            <input class="form-control" type="text" name="values[{{ $i }}][title][{{ $code }}]" value="{{ $row['title'][$code] ?? '' }}">
                                        </div>
                                    @endforeach
                                </div>
                            @empty
                                {{-- empty state --}}
                            @endforelse
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addValue()">Add value</button>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" type="submit">{{ $option->exists ? 'Save changes' : 'Create option' }}</button>
                    <a href="{{ route('catalog.options.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>

    <script>
        function addValue() {
            const wrap = document.getElementById('values-repeater');
            const idx = wrap.querySelectorAll('.border.rounded').length;
            const locales = @json(array_keys(config('app.locales')));
            let html = `<div class="border rounded p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <strong>Value #${idx+1}</strong>
      <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.border').remove()">Remove</button>
    </div>
    <div class="row g-2 mb-2">
      <div class="col-6"><label class="form-label">Sort</label><input class="form-control" type="number" name="values[${idx}][sort_order]" value="0"></div>
      <div class="col-6 d-flex align-items-end">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="values[${idx}][status]" value="1" checked>
          <label class="form-check-label">Active</label>
        </div>
      </div>
    </div>`;
            locales.forEach(code => {
                html += `<div class="mb-2">
      <label class="form-label">${code.toUpperCase()}</label>
      <input class="form-control" type="text" name="values[${idx}][title][${code}]" value="">
    </div>`;
            });
            html += `</div>`;
            wrap.insertAdjacentHTML('beforeend', html);
        }
    </script>
@endsection
