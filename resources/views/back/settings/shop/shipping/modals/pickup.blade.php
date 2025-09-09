{{-- Pickup modal --}}
<div class="modal fade" id="shipping-modal-pickup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">{{ __('back/shop/shipping/pickup.title') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-10 mx-auto">

                        {{-- Title (multi-lang pills like currencies) --}}
                        @include('back.settings.partials.lang-title', ['code'  => 'pickup', 'label' => __('back/shop/shipping/pickup.input_title')])

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('back/shop/shipping.min_order_amount') }}</label>
                                <input type="text" class="form-control" data-config="min">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('back/shop/shipping.geo_zone') }} <span class="small text-muted">{{ __('back/shop/shipping.geo_zone_label') }}</span></label>
                                <select class="form-control" name="geo_zone">
                                    <option value="">{{ __('back/shop/shipping.select_geo') }}</option>
                                    @foreach(($geo_zones ?? []) as $gz)
                                        <option value="{{ $gz->id }}">{{ $gz->title->{current_locale()} ?? ($gz->title->en ?? $gz->id) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Short description (localized) --}}
                        @include('back.settings.partials.lang-description', ['code'  => 'pickup'])

                        <div class="row g-3 mt-3">
                            <div class="col-md-8">
                                <label class="form-label">{{ __('back/shop/shipping/pickup.location') }}</label>
                                <input type="text" class="form-control" data-config="location" placeholder="Store address / pickup desk">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('back/shop/shipping.sort_order') }}</label>
                                <input type="text" class="form-control" name="sort_order" value="0">
                            </div>
                        </div>

                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="status">
                            <label class="form-check-label">{{ __('back/shop/shipping.status_title') }}</label>
                        </div>

                        <input type="hidden" name="id" value="0">
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button class="btn btn-light" data-bs-dismiss="modal">{{ __('back/shop/shipping.cancel') }}</button>
                <button class="btn btn-primary" onclick="saveShipping('pickup');">{{ __('back/shop/shipping.save') }}</button>
            </div>
        </div>
    </div>
</div>
