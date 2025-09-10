@if(!empty($optionTree))
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Options</h6>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addOptionRow()">Add option value</button>
        </div>
        <div class="card-body">
            <p class="text-muted small">Select option values (e.g., Color → Red) and optionally set image, SKU suffix/full, quantity and price adjustments.</p>


            <div class="table-responsive">
                <table class="table table-sm align-middle" id="option-items-table">
                    <thead>
                    <tr>
                        <th style="width:220px">Value</th>
                        <th>Image</th>
                        <th>SKU full</th>
                        <th>SKU suffix</th>
                        <th>Qty</th>
                        <th>Δ Price</th>
                        <th>Override</th>
                        <th>Default</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        // Ako postoji stari input (validation fail) – koristi njega
                        $existing = collect(old('option_items'));

                        if ($existing->isEmpty() && $product->exists) {
                            // lazy-load ako treba
                            $product->loadMissing('optionValues');

                            $existing = $product->optionValues->map(function ($v) {
                                return [
                                    'value_id'       => $v->id,
                                    'product_image_id'=> $v->pivot->product_image_id,
                                    'sku_full'       => $v->pivot->sku_full,
                                    'sku_suffix'     => $v->pivot->sku_suffix,
                                    'quantity'       => $v->pivot->quantity,
                                    'price_delta'    => $v->pivot->price_delta,
                                    'price_override' => $v->pivot->price_override,
                                    'is_default'     => (bool) $v->pivot->is_default,
                                ];
                            })->values();
                        }
                    @endphp

                    @foreach($existing as $i => $row)
                        <tr>
                            <td>
                                <select name="option_items[{{ $i }}][value_id]" class="form-select" required>
                                    <option value="">— choose —</option>
                                    @foreach($optionTree as $opt)
                                        <optgroup label="{{ $opt['title'] }}">
                                            @foreach($opt['values'] as $val)
                                                <option value="{{ $val['id'] }}" @selected((int)($row['value_id'] ?? 0) === (int)$val['id'])>{{ $val['label'] }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="option_items[{{ $i }}][product_image_id]" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach(($productImages ?? collect()) as $img)
                                        <option value="{{ $img->id }}" @selected((int)($row['product_image_id'] ?? 0) === (int)$img->id)>
                                            #{{ $img->id }} — {{ $img->path ?? 'image' }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" class="form-control" name="option_items[{{ $i }}][sku_full]" value="{{ $row['sku_full'] ?? '' }}" placeholder="full SKU"></td>
                            <td><input type="text" class="form-control" name="option_items[{{ $i }}][sku_suffix]" value="{{ $row['sku_suffix'] ?? '' }}" placeholder="-RED"></td>
                            <td style="width:90px"><input type="number" min="0" class="form-control" name="option_items[{{ $i }}][quantity]" value="{{ $row['quantity'] ?? 0 }}"></td>
                            <td style="width:110px"><input type="number" step="0.01" class="form-control" name="option_items[{{ $i }}][price_delta]" value="{{ $row['price_delta'] ?? 0 }}"></td>
                            <td style="width:120px"><input type="number" step="0.01" class="form-control" name="option_items[{{ $i }}][price_override]" value="{{ $row['price_override'] ?? '' }}" placeholder="—"></td>
                            <td class="text-center"><input type="checkbox" class="form-check-input" name="option_items[{{ $i }}][is_default]" value="1" @checked(!empty($row['is_default']))></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">Remove</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>


        </div>
    </div>


    <template id="option-row-template">
        <tr>
            <td>
                <select class="form-select" required data-name="value_id">
                    <option value="">— choose —</option>
                    @foreach($optionTree as $opt)
                        <optgroup label="{{ $opt['title'] }}">
                            @foreach($opt['values'] as $val)
                                <option value="{{ $val['id'] }}">{{ $val['label'] }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-select" data-name="product_image_id">
                    <option value="">— none —</option>
                    @foreach(($productImages ?? collect()) as $img)
                        <option value="{{ $img->id }}">#{{ $img->id }} — {{ $img->path ?? 'image' }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" class="form-control" data-name="sku_full" placeholder="full SKU"></td>
            <td><input type="text" class="form-control" data-name="sku_suffix" placeholder="-RED"></td>
            <td style="width:90px"><input type="number" min="0" class="form-control" data-name="quantity" value="0"></td>
            <td style="width:110px"><input type="number" step="0.01" class="form-control" data-name="price_delta" value="0"></td>
            <td style="width:120px"><input type="number" step="0.01" class="form-control" data-name="price_override" placeholder="—"></td>
            <td class="text-center"><input type="checkbox" class="form-check-input" data-name="is_default" value="1"></td>
            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">Remove</button></td>
        </tr>
    </template>


    <script>
        (function(){
            window.addOptionRow = function(){
                const table = document.getElementById('option-items-table').querySelector('tbody');
                const tpl = document.getElementById('option-row-template');
                const idx = table.querySelectorAll('tr').length;
                const row = tpl.content.firstElementChild.cloneNode(true);
                row.querySelectorAll('[data-name]').forEach(function(el){
                    const key = el.getAttribute('data-name');
                    el.setAttribute('name', `option_items[${idx}][${key}]`);
                });
                table.appendChild(row);
            };
        })();
    </script>
@endif