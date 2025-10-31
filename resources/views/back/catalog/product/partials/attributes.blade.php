{{-- resources/views/back/catalog/product/partials/attributes.blade.php --}}
@if(!empty($attributeTree))
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">@lang('back/products.attributes')</h6>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addAttrRow()">+ @lang('back/products.add_attribute_row', [], 'en')</button>
        </div>
        <div class="card-body">
            <p class="text-muted small">@lang('back/products.attributes_help') {{-- npr. Materijal → 30% Pamuk, 70% Vuna --}}</p>

            <div class="table-responsive">
                <table class="table table-sm align-middle" id="attribute-items-table">
                    <thead>
                    <tr>
                        <th style="width:260px">@lang('back/products.attribute_value')</th>
                        <th style="width:110px">%</th>
                        <th style="width:140px">@lang('back/products.amount')</th>
                        <th style="width:120px">@lang('back/products.unit')</th>
                        <th>@lang('back/products.note')</th>
                        <th class="text-center">@lang('back/products.primary')</th>
                        <th style="width:90px">@lang('back/products.sort')</th>
                        <th class="text-end">@lang('back/products.actions')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $existing = collect(old('attribute_items'));
                        if ($existing->isEmpty() && $product->exists) {
                            $product->loadMissing('attributeValues.attribute.translations');
                            $existing = $product->attributeValues->map(function($v){
                                return [
                                  'value_id'      => $v->id,
                                  'share_percent' => $v->pivot->share_percent,
                                  'amount'        => $v->pivot->amount,
                                  'unit'          => $v->pivot->unit,
                                  'note'          => $v->pivot->note,
                                  'is_primary'    => $v->pivot->is_primary,
                                  'sort_order'    => $v->pivot->sort_order,
                                ];
                            })->values();
                        }
                    @endphp

                    @foreach($existing as $i => $row)
                        <tr>
                            <td>
                                <select name="attribute_items[{{ $i }}][value_id]" class="form-select" required>
                                    <option value="">{{ __('— choose —') }}</option>
                                    @foreach($attributeTree as $attr)
                                        <optgroup label="{{ $attr['title'] }}">
                                            @foreach($attr['values'] as $val)
                                                <option value="{{ $val['id'] }}" @selected((int)($row['value_id'] ?? 0) === (int)$val['id'])>{{ $val['label'] }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" class="form-control" step="0.01" min="0" max="100" name="attribute_items[{{ $i }}][share_percent]" value="{{ $row['share_percent'] ?? '' }}" placeholder="%"></td>
                            <td><input type="number" class="form-control" step="0.0001" min="0" name="attribute_items[{{ $i }}][amount]" value="{{ $row['amount'] ?? '' }}" placeholder="0.0000"></td>
                            <td><input type="text" class="form-control" name="attribute_items[{{ $i }}][unit]" value="{{ $row['unit'] ?? '' }}" maxlength="16" placeholder="%, g, ml, cm ..."></td>
                            <td><input type="text" class="form-control" name="attribute_items[{{ $i }}][note]" value="{{ $row['note'] ?? '' }}" maxlength="255" placeholder=""></td>
                            <td class="text-center"><input type="checkbox" class="form-check-input" name="attribute_items[{{ $i }}][is_primary]" value="1" @checked(!empty($row['is_primary']))></td>
                            <td><input type="number" class="form-control" min="0" name="attribute_items[{{ $i }}][sort_order]" value="{{ $row['sort_order'] ?? 0 }}"></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">@lang('back/products.remove')</button></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <template id="attr-row-template">
        <tr>
            <td>
                <select class="form-select" required data-name="value_id">
                    <option value="">{{ __('— choose —') }}</option>
                    @foreach($attributeTree as $attr)
                        <optgroup label="{{ $attr['title'] }}">
                            @foreach($attr['values'] as $val)
                                <option value="{{ $val['id'] }}">{{ $val['label'] }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </td>
            <td><input type="number" class="form-control" step="0.01" min="0" max="100" data-name="share_percent" placeholder="%"></td>
            <td><input type="number" class="form-control" step="0.0001" min="0" data-name="amount" placeholder="0.0000"></td>
            <td><input type="text" class="form-control" data-name="unit" maxlength="16" placeholder="%, g, ml, cm ..."></td>
            <td><input type="text" class="form-control" data-name="note" maxlength="255"></td>
            <td class="text-center"><input type="checkbox" class="form-check-input" data-name="is_primary" value="1"></td>
            <td><input type="number" class="form-control" min="0" data-name="sort_order" value="0"></td>
            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">@lang('back/products.remove')</button></td>
        </tr>
    </template>

    <script>
        (function(){
            window.addAttrRow = function(){
                const table = document.getElementById('attribute-items-table').querySelector('tbody');
                const tpl   = document.getElementById('attr-row-template');
                const idx   = table.querySelectorAll('tr').length;
                const row   = tpl.content.firstElementChild.cloneNode(true);
                row.querySelectorAll('[data-name]').forEach(function(el){
                    const key = el.getAttribute('data-name');
                    el.setAttribute('name', `attribute_items[${idx}][${key}]`);
                });
                table.appendChild(row);
            };
        })();
    </script>
@endif
