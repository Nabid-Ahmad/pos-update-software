
<div class="row">
    <div class="col-md-12">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="4" class="p-1" style="background-color: #f1f8ff;">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-info text-white font-weight-bold" style="min-width: 140px;"><i class="fa fa-barcode"></i> &nbsp; Barcode [F2]</span>
                            </div>
                            <input type="text" id="barcode_input" class="form-control barcode-input" placeholder="Scan barcode here..." autofocus autocomplete="off" style="font-size: 16px; font-weight: bold; height: 35px;">
                        </div>
                    </th>
                </tr>
                <tr>
                    <th width="40%">Product Name [F3]</th>
                    <th width="20%">Quantity</th>
                    <th width="20%">Unit Cost</th>
                    <th width="20%">Free Quantity</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        <select class="form-control select-product select2 mr-0" style="width: 100%">
                            <option value="">Select</option>
                            @foreach($products as $key => $product)
                                <option value="{{ $product->id }}"
                                        data-product-name="{{ $product->product_name }}"
                                        data-product-code="{{ $product->product_code }}"
                                        data-barcode="{{ optional($product->barcode)->barcode_number }}"
                                        data-product-cost="{{ $product->product_cost }}"
                                        data-has-serial="{{ $product->has_serial }}"
                                    >
                                    {{ $product->product_name }} ({{ $product->product_code }}) {{ ($product->has_serial==1)? '--Serial' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input class="select-quantity form-control" type="text" value="">
                    </td>
                    <td>
                        <input class="select-unit-cost form-control" type="text" value="">
                    </td>
                    <td>
                        <input class="select-free-quantity form-control" type="text" value="">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
