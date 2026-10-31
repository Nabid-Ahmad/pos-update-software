<div class="row border-bottom mb-2 pb-1" style="background-color: #f1f8ff;">
    <div class="col-md-12 p-2">
        <div class="input-group input-group-sm">
            <div class="input-group-prepend">
                <span class="input-group-text bg-info text-white font-weight-bold" style="min-width: 140px;"><i class="fa fa-barcode"></i> &nbsp; Barcode [F2]</span>
            </div>
            <input type="text" id="barcode_input" class="form-control barcode-input" placeholder="Scan barcode here..." autofocus autocomplete="off" style="font-size: 16px; font-weight: bold; height: 35px;">
        </div>
    </div>
</div>

<div class="row border-bottom mb-2 pb-1">
    <div class="col-md-5">
        <div class="form-group mb-0">
            <label class="font-weight-bold">Product Name [F3]</label>
            <select name="fk_product_id" class="form-control select-product select2">
                <option value="">Select Product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}"
                            data-product-name="{{ $product->product_name }}"
                            data-product-code="{{ $product->product_code }}"
                            data-barcode="{{ optional($product->barcode)->barcode_number }}"
                            data-product-cost="{{ $product->product_cost ?? 0 }}"
                            data-product-price="{{ $product->product_price ?? 0 }}"
                            data-tax-percent="{{ $product->tax ?? 0 }}"
                            data-available-quantity="{{ $product->available_quantity }}"
                            data-has-serial="{{ $product->has_serial }}"
                            data-supplier-id="{{ $product->supplier_id }}"
                    >
                        {{ $product->product_name }} ({{ $product->product_code }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2 border-left px-1">
        <div class="form-group mb-0">
            <label class="font-weight-bold d-block text-center">Available Qty</label>
            <input type="text" class="form-control select-available-quantity text-center bg-light" readonly style="height: 30px;">
        </div>
    </div>
    <div class="col-md-2 border-left px-1">
        <div class="form-group mb-0">
            <label class="font-weight-bold d-block text-center">Quantity</label>
            <input type="text" class="form-control select-quantity text-center" onkeyup="checkAvailableQty()" style="height: 30px;">
        </div>
    </div>
    <div class="col-md-3 border-left px-1">
        <div class="form-group mb-0">
            <label class="font-weight-bold d-block text-center">Unit Price</label>
            <input type="text" class="form-control select-unit-price text-center" style="height: 30px;">
            <input type="hidden" class="select-unit-cost">
        </div>
    </div>
</div>

<div class="row product-serial-section mb-2" style="display: none">
    <div class="col-md-12">
        <div class="input-group input-group-sm">
            <div class="input-group-prepend">
                <span class="input-group-text">Serial Number</span>
            </div>
            <input type="text" id="searchBySerial" class="form-control" placeholder="Scan or type serial and press enter" onkeypress="getProductBySerial(this)">
        </div>
    </div>
</div>
