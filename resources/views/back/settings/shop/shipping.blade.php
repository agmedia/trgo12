{{-- resources/views/back/settings/shop/shipping.blade.php --}}
@extends('back.layouts.base-admin')

@section('title', __('back/shop/shipping.title', [], current_locale()) ?? 'Shipping')

@php
    $locales = $locales ?? config('shop.locales', ['hr'=>'Hrvatski','en'=>'English']);
@endphp

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header align-items-center justify-content-between d-flex">
                    <div>
                        <h5 class="mb-1">{{ __('back/shop/shipping.list_title') }}</h5>
                        <div class="small text-muted">{{ __('back/shop//shipping.subtitle') }}</div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th style="width:56px;">#</th>
                                <th>{{ __('back/shop/shipping.table_title', [], current_locale()) ?? 'Title' }}</th>
                                <th class="text-center">{{ __('back/shop/shipping.status_title') ?? 'Status' }}</th>
                                <th class="text-center">{{ __('back/shop/shipping.sort_order') ?? 'Sort' }}</th>
                                <th class="text-end" style="width:120px;">{{ __('back/shop/shipping.actions') ?? 'Actions' }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php $i=1; @endphp
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>
                                        @include('back.settings.partials.lang-list-title', ['item' => $item])
                                    </td>
                                    <td class="text-center">
                                        @include('back.settings.partials.list-status', ['item' => $item])
                                    </td>
                                    <td class="text-center">{{ $item->sort_order ?? 0 }}</td>
                                    <td class="text-end">
                                        @include('back.settings.partials.list-action-buttons', ['item' => $item, 'editHandler' => 'openShippingModal', 'deleteHandler' => 'deleteShipping'])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">{{ __('back/shop/shipping.empty_list') ?? 'No shipping methods…' }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- provider modals --}}
    @foreach(($providers ?? []) as $code => $meta)
        @php $blade = ($meta['driver']::backModalBlade()) @endphp
        @includeIf($blade, ['code' => $code, 'locales' => $locales, 'geo_zones' => $geo_zones ?? collect()])
    @endforeach
@endsection

@push('scripts')
    <script>
        if (window.axios) {
            window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            const token = document.querySelector('meta[name="csrf-token"]');
            if (token) window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
        }

        // locales for pills
        const SHIP_LOCALES = @json(collect($locales)->map(function($name,$code){ return ['code'=>$code,'name'=>$name]; })->values());

        window.openShippingModal = function(item) {
            const code = item.code;
            const modalEl = document.getElementById('shipping-modal-' + code);
            if (!modalEl) return;

            modalEl.querySelector('form')?.reset();

            // base fields
            const idEl     = modalEl.querySelector('input[name="id"]');
            const sortEl   = modalEl.querySelector('input[name="sort_order"]');
            const statusEl = modalEl.querySelector('input[name="status"]');
            if (idEl) idEl.value = item.id ?? 0;
            if (sortEl) sortEl.value = item.sort_order ?? 0;
            if (statusEl) statusEl.checked = !!item.status;

            // titles
            SHIP_LOCALES.forEach(l => {
                const el = modalEl.querySelector('#title-' + code + '-' + l.code);
                if (el) el.value = (item.title && item.title[l.code]) ? item.title[l.code] : '';
            });

            // optional geo zone
            const gz = modalEl.querySelector('select[name="geo_zone"]');
            if (gz) gz.value = (item.geo_zone ?? '');

            // provider config
            modalEl.querySelectorAll('[data-config]').forEach(input => {
                const key = input.getAttribute('data-config');
                let val = '';
                if (item.config) {
                    val = key.split('.').reduce((acc, k) => (acc && typeof acc === 'object' && (k in acc) ? acc[k] : ''), item.config);
                }
                // set value for input/textarea/select
                if (input.tagName === 'SELECT') {
                    input.value = (val ?? input.getAttribute('data-default') ?? '');
                } else {
                    input.value = (val ?? input.getAttribute('data-default') ?? '');
                }
            });

            new bootstrap.Modal(modalEl).show();
        };

        window.saveShipping = function(code) {
            const modalEl = document.getElementById('shipping-modal-' + code);
            if (!modalEl) return;

            const id        = parseInt(modalEl.querySelector('input[name="id"]')?.value || '0', 10);
            const sortOrder = parseInt(modalEl.querySelector('input[name="sort_order"]')?.value || '0', 10);
            const status    = !!modalEl.querySelector('input[name="status"]')?.checked;

            const title = {};
            SHIP_LOCALES.forEach(l => {
                const el = modalEl.querySelector('#title-' + code + '-' + l.code);
                title[l.code] = (el?.value || '').trim();
            });

            // config (supports nested with "a.b.c")
            const config = {};
            modalEl.querySelectorAll('[data-config]').forEach(input => {
                const parts = (input.getAttribute('data-config') || '').split('.');
                let cur = config;
                for (let i = 0; i < parts.length; i++) {
                    const p = parts[i];
                    if (i === parts.length - 1) cur[p] = input.value;
                    else cur = (cur[p] = cur[p] && typeof cur[p] === 'object' ? cur[p] : {});
                }
            });

            const gzEl = modalEl.querySelector('select[name="geo_zone"]');
            const geo_zone = gzEl ? (parseInt(gzEl.value || '0', 10) || null) : null;

            const payload = { id, code, title, sort_order: sortOrder, status, config };
            if (geo_zone !== null) payload.geo_zone = geo_zone;

            axios.post('/api/v1/settings/shipping', { data: payload })
            .then(r => { if (r.data?.success) location.reload(); else (errorToast && errorToast.fire(r.data.message || 'Error')); })
            .catch(() => errorToast && errorToast.fire('Network error'));
        };

        window.deleteShipping = function(id) {
            if (!id) return;
            axios.delete('/api/v1/settings/shipping', { data: { id } })
            .then(r => { if (r.data?.success) location.reload(); else (errorToast && errorToast.fire(r.data.message || 'Error')); })
            .catch(() => errorToast && errorToast.fire('Network error'));
        };
    </script>
@endpush
