@extends('admin.master')
@push('style')
    <style>
        .add-new-btn {
            border-radius: 0 0 0 0;
        }

        .add-new-product-button {
            padding: 4.5px;
            border-radius: 0px 5px 5px 0px;
            visibility: hidden;
        }

        .new-customer-btn {
            padding: 4.5px;
            border-radius: 0px 5px 5px 0px;
            margin-top: 28px;
        }

        .loading {
            display: none;
            justify-content: center;
            align-items: center;
            width: 100%;
            position: absolute;
            height: 100%;
            z-index: 11111;
            background: #ffffffc7;
        }

        td {
            border: 1px solid black;
        }
    </style>
@endpush

@section('content')

    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                @if($errors->any())
                    <div class="alert-danger">
                        <ul class="list-unstyled">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="form-horizontal" method="POST" action="{{ route('sale-returns.store') }}">
                    @csrf

                    <div class="tile">
                        <a href="{{ route('sale-returns.index') }}" class="btn btn-primary pull-right"
                           style="float: right;">
                            <i class="fa fa-eye"></i> View Returns
                        </a>
                        <h3 class="tile-title">Sale Return</h3>
                        <hr>
                        <div class="tile-body">
                            <div class="row">


                                {{-- customer --}}
                                <div class="col-sm-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Customer<span class="text-danger" style="font-size: 16px;">*</span></label>
                                                <select name="customer_id" class="form-control select2" id="customer-id"
                                                        onchange="loadCustomerSaleInvoices(this)" required>
                                                    <option class="text-center" value="">-Select-</option>
                                                    @foreach($customers as $key => $customer)
                                                        <option value="{{ $customer->id }}"
                                                                data-balance="{{ $customer->balance }}" {{ $customer->id == old('customer_id') ? 'selected' : '' }}>
                                                            {{ $customer->customerId }} - {{ $customer->name }}{{ $customer->phone ? ' - ' . $customer->phone : '' }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Reference No</label>
                                                <input name="reference" value="{{ old('reference') }}"
                                                       class="form-control" type="text" placeholder="Reference">
                                                <div class="text-danger">
                                                    {{ $errors->has('reference') ? $errors->first('reference') : '' }}
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-md-4">
                                            <div class="form-group ">
                                                <label class="control-label">Date</label>
                                                <input name="date" value="{{ old('date') ?? date('Y-m-d') }}"
                                                       class="form-control dateField" type="text" placeholder="Date">
                                                <div class="text-danger">
                                                    {{ $errors->has('date') ? $errors->first('date') : '' }}
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>


                                {{-- invoice --}}
                                <div class="col-sm-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Invoice (Optional)</label>
                                                <select name="sale_id" class="form-control select2"
                                                        onchange="loadInvoiceProducts(this)" id="invoice-id">
                                                    <option class="text-center" value="">-Select-</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Product Serial</label>
                                                <input type="text" class="form-control" onkeypress="getInvoiceId()"
                                                       onkeyup="getInvoiceId()" id="serial_id">
                                            </div>
                                        </div>
                                        @if(isset($supplierWiseReturnEnabled) && $supplierWiseReturnEnabled)
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Supplier (Optional)</label>
                                                <select name="supplier_id" class="form-control select2"
                                                        onchange="loadSupplierProducts(this)" id="supplier-id">
                                                    <option class="text-center" value="">-Select-</option>
                                                    @foreach($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>


                                {{-- product return --}}
                                <div class="col-sm-12">
                                    <div class="row">
                                        <div class="col-md-12 mb-2" id="discount-alert-box" style="display: none;">
                                            <div class="alert alert-info py-2 mb-1" style="background-color: #e3f2fd; border-color: #b3e5fc; color: #0d47a1; border-left: 5px solid #0288d1; border-radius: 4px;">
                                                <i class="fa fa-info-circle"></i> <span id="discount-alert-text"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Product (Return)<span class="text-danger" style="font-size: 16px;">*</span></label>
                                                <select class="form-control select2" onchange="selectProduct(this)" id="product-id" required>
                                                    <option class="text-center" value="">-Select-</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}"
                                                                data-product_stock="{{ $product->product_stock->available_quantity ?? 0 }}"
                                                                data-sale_id=""
                                                                data-quantity="9999"
                                                                data-price="{{ $product->product_price }}"
                                                                data-original_price="{{ $product->product_price }}"
                                                                data-product_discount="0"
                                                                data-invoice_discount_share="0"
                                                                data-warrany="0"
                                                                data-guarrantee="0">
                                                            {{ $product->product_name }} ({{ $product->product_code }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Returnable Qty</label>
                                                <input class="returnable-quantity form-control" type="text" value="" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Quantity<span class="text-danger" style="font-size: 16px;">*</span></label>
                                                <input class="select-quantity form-control"
                                                       onkeyup="checkValidQty(this)"
                                                       onkeypress="return selectProductQuantity(event)" type="text"
                                                       value="">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Price</label>
                                                <input class="select-unit-cost form-control"
                                                       onkeypress="return productPriceEnterKeyEvent(event)" type="text"
                                                       value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                        <tr>
                                            <th width="3%" class="text-center">Sl.</th>
                                            <th width="28%">Product Name</th>
                                            <th>Condition</th>
                                            <th width="11%" class="text-center">Returnable Qty</th>
                                            <th width="11%" class="text-center">Return Qty</th>
                                            <th width="11%" class="text-right pr-2">Price</th>
                                            <th width="11%" class="text-right">Amount</th>
                                            <th width="1%"></th>
                                        </tr>
                                        </thead>

                                        <tbody class="product-return-tbody">
                                        <tr class="not-found-row">
                                            <td class="text-danger text-center py-3" colspan="20">No records added!</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- product exchange --}}
                                <div class="col-sm-12">
                                    <h3 class="mt-3"><i class="fa fa-exchange"></i> Exchange Product (Optional)</h3>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Product (Exchange)</label>
                                                <select class="form-control select2" onchange="selectExProduct(this)" id="ex-product-id">
                                                    <option class="text-center" value="">-Select-</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}"
                                                                data-price="{{ $product->product_price }}"
                                                                data-stock="{{ $product->product_stock->available_quantity ?? 0 }}">
                                                            {{ $product->product_name }} ({{ $product->product_code }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="control-label">Quantity</label>
                                                <input class="ex-select-quantity form-control" onkeypress="return selectExProductQuantity(event)" type="text" value="">
                                                <small id="ex-available-stock" class="font-weight-bold"></small>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Price</label>
                                                <input class="ex-select-unit-cost form-control" onkeypress="return exProductPriceEnterKeyEvent(event)" type="text" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-info mt-4 text-white" onclick="insertExchangeItem()"><i class="fa fa-plus"></i> Add Exchange</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                        <tr class="bg-info text-white">
                                            <th colspan="8"><i class="fa fa-exchange"></i> Exchange Products</th>
                                        </tr>
                                        <tr>
                                            <th width="3%" class="text-center">Sl.</th>
                                            <th width="28%">Product Name</th>
                                            <th colspan="2"></th>
                                            <th width="11%" class="text-center">Qty</th>
                                            <th width="11%" class="text-right pr-2">Price</th>
                                            <th width="11%" class="text-right">Amount</th>
                                            <th width="1%"></th>
                                        </tr>
                                        </thead>
                                        <tbody class="product-exchange-tbody">
                                        <tr class="ex-not-found-row">
                                            <td class="text-info text-center py-3" colspan="20">No exchange records added!</td>
                                        </tr>
                                        </tbody>

                                    </table>

                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Comments</label>
                                                <textarea name="comment" class="form-control" placeholder="Add any comments here..." style="height:100px;"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-bordered">
                                                <tr>
                                                    <th class="text-right" style="vertical-align: middle;">Product Return Value (-) :</th>
                                                    <td width="40%">
                                                        <input type="text" class="form-control total-amount text-right" value="0" readonly name="total_amount">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-right" style="vertical-align: middle;">Product Exchange Value (+) :</th>
                                                    <td>
                                                        <input type="text" class="form-control ex-total-amount text-right" value="0" readonly name="ex_total_amount">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-right" style="vertical-align: middle;">Previous Due (+) :</th>
                                                    <td>
                                                        <input type="text" class="form-control customer-previous-due text-right" value="0" readonly name="previous_due">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-right" style="vertical-align: middle; font-weight: bold; font-size: 16px;">Current Balance :</th>
                                                    <td>
                                                        <input type="text" class="form-control grand-total-amount text-right" style="font-weight: bold; font-size: 16px;" value="0" readonly name="grand_total_amount" id="grand_total_amount">
                                                    </td>
                                                </tr>
                                                <tr id="return-amount-row">
                                                    <th class="text-right" style="vertical-align: middle;">Cash Refund to Customer :</th>
                                                    <td>
                                                        <input type="text" onkeyup="calculateTotal()" autocomplete="off" onkeypress="return event.charCode >= 46 && event.charCode <= 57" class="form-control return-amount text-right" value="0" name="return_amount">
                                                    </td>
                                                </tr>
                                                <tr id="received-amount-row" style="display: none;">
                                                    <th class="text-right" style="vertical-align: middle; color: #dc3545;">Cash Received from Customer :</th>
                                                    <td>
                                                        <input type="text" onkeyup="calculateTotal()" autocomplete="off" onkeypress="return event.charCode >= 46 && event.charCode <= 57" class="form-control received-amount text-right" value="0" name="received_amount">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-right" style="vertical-align: middle; font-weight: bold;">Final Customer Due :</th>
                                                    <td>
                                                        <input type="text" class="form-control current-balance text-right" style="font-weight: bold;" value="0" readonly name="current_balance">
                                                    </td>
                                                </tr>
                                                @php
                                                    $showSmsInvoice = \App\SoftwareSetting::where('title', 'Send Invoice Sms To Customer')->where('options', 'yes')->where('fk_company_id', auth()->user()->fk_company_id)->count() > 0;
                                                @endphp
                                                @if($showSmsInvoice)
                                                <tr>
                                                    <td colspan="2" class="text-right" style="border: none;">
                                                        <div class="custom-control custom-checkbox custom-control-inline float-right">
                                                            <input type="checkbox" id="is_send_message" name="is_send_message" class="custom-control-input" value="1" checked>
                                                            <label class="custom-control-label text-success font-weight-bold" for="is_send_message" style="font-size: 13px;"><i class="fa fa-commenting"></i> Send SMS</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td colspan="2" class="text-right" style="border: none;">
                                                        <button class="btn btn-primary btn-block mt-2" type="submit" onclick="myvalidation()">
                                                            <i class="fa fa-fw fa-lg fa-check-circle"></i> Add Return / Exchange
                                                        </button>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    </div>
                                </div>



                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

@endsection

@section('footer-script')
    <script src="{{ asset('jq/select2Loads.js') }}"></script>
    <script src="{{ asset('assets/admin/jq/sale_return.js') }}"></script>
    <script>

        function myvalidation() {
            let keycode = (evnt.keyCode ? evnt.keyCode : evnt.which);
            if (keycode != '13') {
                alert('ender from price')
            }
            return keycode >= 46 && keycode <= 57
        }

        function calculateTotal() {
            let totalAmount = parseFloat($('.total-amount').val()) || 0;
            let exTotalAmount = parseFloat($('.ex-total-amount').val()) || 0;
            let previousDue = parseFloat($('.customer-previous-due').val()) || 0;
            let grandTotal = previousDue - totalAmount + exTotalAmount;
            
            let returnAmountBox = $('#return-amount-row');
            let receivedAmountBox = $('#received-amount-row');
            
            if (grandTotal > 0) {
                // Customer owes money to shop
                $('.return-amount').val(0); // Forcibly set to 0
                returnAmountBox.hide();
                receivedAmountBox.show();
                $('.grand-total-amount').css('color', '#dc3545'); // Red color for debt
            } else {
                // Shop owes customer
                $('.received-amount').val(0); // Forcibly set to 0
                receivedAmountBox.hide();
                returnAmountBox.show();
                $('.grand-total-amount').css('color', '#28a745'); // Green color for credit
            }

            let returnAmount = parseFloat($('.return-amount').val()) || 0;
            let receivedAmount = parseFloat($('.received-amount').val()) || 0;

            $('.grand-total-amount').val(grandTotal.toFixed(2));
            $('.current-balance').val((grandTotal + returnAmount - receivedAmount).toFixed(2));
        }

        function loadCustomerSaleInvoices(object) {
            $('.product-return-tbody').empty()
            $('.product-return-tbody').append('<tr class="not-found-row"><td class="text-danger text-center py-3" colspan="20">No records added!</td></tr>')
            $('#discount-alert-box').hide();

            $('.total-amount').val(0)
            $('.customer-previous-due').val(0)
            $('.return-amount').val(0)
            $('.received-amount').val(0)
            $('.grand-total-amount').val(0)
            $('.current-balance').val(0)

            if ($(object).val() != '') {
                $('.customer-previous-due').val($('#customer-id option:selected').data('balance'))
            }

            let invoice = $('#invoice-id')
            invoice.empty()
            invoice.append('<option value="">-Select-</option>')

            $.get("/get-customer-invoices", {customer_id: $(object).val()}, function (data, status) {
                $(data).each(function (index, item) {
                    invoice.append('<option value="' + item.id + '">' + item.invoice_no + '</option>')
                })
                $('#invoice-id').select2()
                $('#invoice-id').trigger('change')
            })
        }

        function loadInvoiceProducts(object) {
            let invoice_id = $('#invoice-id').val()
            let customer_id = $('#customer-id').val()
            $('#discount-alert-box').hide();

            let products = $('#product-id')
            products.empty()
            products.append('<option value="">-Select-</option>')

            $.get("/get-customer-buying-products", {
                customer_id: customer_id,
                invoice_id: invoice_id
            }, function (data, status) {
                $(data).each(function (index, item) {
                    products.append('<option data-product_stock="' + item.product_stock + '" data-sale_id="' + item.sale_id + '" data-quantity="' + item.quantity + '" data-price="' + item.price + '" data-original_price="' + item.original_price + '" data-product_discount="' + item.product_discount + '" data-invoice_discount_share="' + item.invoice_discount_share + '" data-warrany="' + item.warranty_left + '" data-guarrantee="' + item.guarantee_left + '" value="' + item.id + '">' + item.name + ' (' + item.code + ')</option>')
                })
                $('#product-id').select2()
                $('#product-id').trigger('change')
            })
        }

        function loadSupplierProducts(object) {
            let supplier_id = $('#supplier-id').val()
            let customer_id = $('#customer-id').val()

            if (!supplier_id) {
                // If supplier is deselected, reset to customer's global balance
                $('.customer-previous-due').val($('#customer-id option:selected').data('balance') || 0);
                calculateTotal();
                return;
            }

            // Fetch Supplier-wise Due
            $.get("/get-supplier-customer-due", {
                customer_id: customer_id,
                supplier_id: supplier_id
            }, function (data, status) {
                $('.customer-previous-due').val(data.due);
                calculateTotal();
            });

            let products = $('#product-id')
            let exchange_products = $('#ex-product-id')
            
            products.empty()
            products.append('<option class="text-center" value="">-Select-</option>')
            
            exchange_products.empty()
            exchange_products.append('<option class="text-center" value="">-Select-</option>')

            $.get("/get-supplier-products", {
                supplier_id: supplier_id
            }, function (data, status) {
                $(data).each(function (index, item) {
                    let stock = item.product_stock ? item.product_stock.available_quantity : 0;
                    
                    let returnOptionHtml = '<option ' + 
                        'data-product_stock="' + stock + '" ' +
                        'data-sale_id="" ' +
                        'data-quantity="9999" ' +
                        'data-price="' + item.product_price + '" ' +
                        'data-original_price="' + item.product_price + '" ' +
                        'data-product_discount="0" ' +
                        'data-invoice_discount_share="0" ' +
                        'data-warrany="0" ' +
                        'data-guarrantee="0" ' +
                        'value="' + item.id + '">' + item.product_name + ' (' + item.product_code + ')</option>';
                        
                    let exchangeOptionHtml = '<option ' + 
                        'data-price="' + item.product_price + '" ' +
                        'data-stock="' + stock + '" ' +
                        'value="' + item.id + '">' + item.product_name + ' (' + item.product_code + ')</option>';
                        
                    products.append(returnOptionHtml);
                    exchange_products.append(exchangeOptionHtml);
                })
                $('#product-id').select2()
                $('#ex-product-id').select2()
            })
        }

        function getInvoiceId() {
            let serial_id = $('#serial_id').val()
            $.get("/get-invoice-id-for-serial-product", {serial_id: serial_id}, function (dataz, status) {
                if (dataz != null) {
                    $('#product-id').html('')
                    $('#invoice-id').html('')
                    $('#customer-id').html('')
                    loadProductsBySerial(dataz)
                }
            });
        }

        var ajaxLoading = false;

        function loadProductsBySerial(dataz) {
            let saleId = dataz.saleId
            let invoice_id = dataz.invoice_no
            let customer_id = dataz.customer_id
            let products = $('#product-id')
            let InvoiceId = $('#invoice-id')
            let customer = $('#customer-id')
            $('#discount-alert-box').hide();

            $('#customer-id').html('')
            if (!ajaxLoading) {
                ajaxLoading = true;
                $.get("/get-customer-data", {customer_id: customer_id}, function (customerdata, status) {
                    ajaxLoading = false;
                    $(customerdata).each(function (index, item) {
                        let formattedId = item.id ? '#c-' + String(item.id).padStart(5, '0') : '';
                        let text = (formattedId ? formattedId + ' - ' : '') + item.name + (item.phone ? ' - ' + item.phone : '');
                        customer.append('<option value="' + item.id + '" data-balance="' + item.current_balance + '" selected>' + text + '</option>');
                    })
                    customer.select2()
                })
            }

            $('#invoice-id').html('')
            InvoiceId.append('<option class="text-center" value="' + saleId + '" selected>' + invoice_id + '</option>')
            InvoiceId.select2()

            $('#product-id').html('')
            products.append('<option value="">-All-</option>')

            $.get("/get-customer-buying-products", {
                customer_id: customer_id,
                invoice_id: saleId
            }, function (productsdata, status) {
                $(productsdata).each(function (index, item) {
                    products.append('<option data-product_stock="' + item.product_stock + '" data-sale_id="' + item.sale_id + '" data-quantity="' + item.quantity + '" data-price="' + item.price + '" data-original_price="' + item.original_price + '" data-product_discount="' + item.product_discount + '" data-invoice_discount_share="' + item.invoice_discount_share + '" data-warrany="' + item.warranty_left + '" data-guarrantee="' + item.guarantee_left + '" value="' + item.id + '">' + item.name + ' (' + item.code + ')</option>')
                })
                products.select2()
                products.trigger('change')
            })
        }

        function selectProduct(obejct) {
            let selectedOpt = $(obejct).find('option:selected');
            if (selectedOpt.val() === '') {
                $('#discount-alert-box').hide();
                return;
            }

            let productCost = selectedOpt.attr('data-price') || 0;
            let quantity = selectedOpt.attr('data-quantity');
            let origPrice = selectedOpt.attr('data-original_price') || productCost;
            let prodDiscount = selectedOpt.attr('data-product_discount') || 0;
            let invDiscountShare = selectedOpt.attr('data-invoice_discount_share') || 0;

            if (quantity == null) {
                quantity = selectedOpt.attr('data-product_stock') || 0;
            }
            $('.select-unit-cost').val(productCost)
            $('.returnable-quantity').val(Math.round(quantity))

            $('.select-product').select2();
            $('.select-quantity').focus()

            if (parseFloat(prodDiscount) > 0 || parseFloat(invDiscountShare) > 0) {
                let msg = `<strong>Discount Details:</strong> This product original unit price was <strong>${Number(origPrice).toFixed(2)} TK</strong>. `;
                if (parseFloat(prodDiscount) > 0) {
                    msg += `Product-level discount: <strong>-${Number(prodDiscount).toFixed(2)} TK</strong>. `;
                }
                if (parseFloat(invDiscountShare) > 0) {
                    msg += `Invoice-level discount share: <strong>-${Number(invDiscountShare).toFixed(2)} TK</strong>. `;
                }
                msg += `The net refund price is set to <strong>${Number(productCost).toFixed(2)} TK</strong>.`;
                $('#discount-alert-text').html(msg);
                $('#discount-alert-box').slideDown(200);
            } else {
                $('#discount-alert-box').slideUp(200);
            }
        }

        function selectProductQuantity(evnt) {
            let keycode = (evnt.keyCode ? evnt.keyCode : evnt.which);
            if (keycode == '13') {
                evnt.preventDefault()
                $('.select-unit-cost').focus()
            }
            return keycode >= 46 && keycode <= 57
        }

        function productPriceEnterKeyEvent(evnt) {
            let keycode = (evnt.keyCode ? evnt.keyCode : evnt.which);
            if (keycode == '13') {
                insertNewItem()
            }
            return keycode >= 46 && keycode <= 57
        }

        function checkValidQty(object) {
            let returnable_qty = Number($('.returnable-quantity').val() | 0)
            let return_qty = Number($(object).val() | 0)

            if (return_qty > returnable_qty || returnable_qty < 1) {
                alert('You can not return more than ' + returnable_qty + ' quantity')
                $(object).val(returnable_qty)
            }
        }

        function insertNewItem() {
            let tableTobody = $('.product-return-tbody')
            let quantity = $('.select-quantity').val()
            let product = $('#product-id option:selected')
            let price = $('.select-unit-cost').val()
            let return_qty = $('.returnable-quantity').val()

            if(quantity < 1){
                alert("nothing for return!");
                return 1;
            }

            let sale_id = product.data('sale_id')
            var product_serials = '';

            let tr = `<tr>
                            <td class="item-serial text-center"></td>
                            <td>
                                <input type="hidden" name="product_ids[]" value="${product.val()}">
                                ${product.text()}
                                <p>${product.data('warrany')} ${product.data('guarrantee')}</p>
                                <p id="serial"></p>
                            </td>
                            <td>
                                <select name="condition_type[]" class="form-control select2 condition">
                                    <option value="Good" selected>Good</option>
                                    <option value="Damage">Damage</option>
                                </select>
                            </td>
                            <td class="text-center returnable-qty">${return_qty | 0}</td>
                            <td class="text-center quantity-td">
                                <input type="hidden" class="product-quantity" name="quantities[]" value="${quantity}">
                                ${quantity}
                            </td>
                            <td class="text-right">
                                <input type="hidden" class="product-price" name="prices[]" value="${price}">
                                ${price}
                            </td>
                            <td class="text-right subtotal">${Number(quantity) * Number(price)}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm" title="Remove This Row" onclick="removeRow(this)">
                                    <i class="fa fa-times"></i>
                                </button>
                            </td>
                        </tr>`

            tableTobody.append(tr)
            $('.condition').select2();
            setItemSerial()

            $.get("/get-customer-serial-products", {sale_id: sale_id}, function (data, status) {
                $(data).each(function (index, item) {
                    product_serials = `<input class="product-serial" type="hidden" id="serial${item.serial}" name="product_serials[${item.product_id}][]" value="${item.serial}">
                                <span class=" mr-1" style="font-size: 12px">${item.serial}<span class="d-print-none" onmouseover="this.style.color='red'" onmouseout="this.style.color='black'" onclick="SerialItem(this)"><i class="fa fa-times-circle"></i></span></span>`
                    $('#serial').append(product_serials)
                })
            })
        }

        function setItemSerial() {
            $('.not-found-row').remove()
            let totalAmount = 0

            $('.item-serial').each(function (index) {
                $(this).text((index + 1))
                totalAmount += Number($(this).closest('tr').find('.subtotal').text())
            })
            $('.total-amount').val(totalAmount)
            calculateTotal()
        }

        function selectExProduct(object) {
            let selectedOpt = $(object).find('option:selected')
            let productPrice = selectedOpt.data('price')
            let productStock = parseFloat(selectedOpt.data('stock')) || 0

            $('.ex-select-unit-cost').val(productPrice)
            $('.ex-select-quantity').val(1)

            // Available stock দেখাও
            if (productStock > 0) {
                $('#ex-available-stock')
                    .text('Available: ' + productStock + ' pcs')
                    .removeClass('text-danger')
                    .addClass('text-info')
            } else {
                $('#ex-available-stock')
                    .text('Out of Stock!')
                    .removeClass('text-info')
                    .addClass('text-danger')
            }

            $('.ex-select-quantity').focus()
        }

        function selectExProductQuantity(evnt) {
            let keycode = (evnt.keyCode ? evnt.keyCode : evnt.which);
            if (keycode == '13') {
                evnt.preventDefault()
                $('.ex-select-unit-cost').focus()
            }
            return keycode >= 46 && keycode <= 57
        }

        function exProductPriceEnterKeyEvent(evnt) {
            let keycode = (evnt.keyCode ? evnt.keyCode : evnt.which);
            if (keycode == '13') {
                insertExchangeItem()
            }
            return keycode >= 46 && keycode <= 57
        }

        function insertExchangeItem() {
            let tableTobody = $('.product-exchange-tbody')
            let quantity = parseFloat($('.ex-select-quantity').val()) || 0
            let product = $('#ex-product-id option:selected')
            let price = $('.ex-select-unit-cost').val()
            let availableStock = parseFloat(product.data('stock')) || 0

            if (product.val() == '' || quantity < 1) {
                alert("Please select product and quantity!");
                return;
            }

            // Stock validation
            if (quantity > availableStock) {
                alert('Available stock is only ' + availableStock + ' pcs! You cannot exchange more than available stock.');
                $('.ex-select-quantity').val(availableStock)
                return;
            }

            let tr = `<tr>
                            <td class="ex-item-serial text-center"></td>
                            <td>
                                <input type="hidden" name="ex_product_ids[]" value="${product.val()}">
                                ${product.text()}
                                <br><small class="text-info">Stock: ${availableStock} pcs</small>
                            </td>
                            <td colspan="2"></td>
                            <td class="text-center">
                                <input type="hidden" name="ex_quantities[]" value="${quantity}">
                                ${quantity}
                            </td>
                            <td class="text-right">
                                <input type="hidden" name="ex_prices[]" value="${price}">
                                ${price}
                            </td>
                            <td class="text-right ex-subtotal">${Number(quantity) * Number(price)}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm" title="Remove This Row" onclick="removeExRow(this)">
                                    <i class="fa fa-times"></i>
                                </button>
                            </td>
                        </tr>`

            tableTobody.append(tr)
            setExItemSerial()

            // Reset fields
            $('#ex-product-id').val('').trigger('change')
            $('.ex-select-quantity').val('')
            $('.ex-select-unit-cost').val('')
            $('#ex-available-stock').text('')
        }

        function setExItemSerial() {
            $('.ex-not-found-row').remove()
            let exTotalAmount = 0
            $('.ex-item-serial').each(function (index) {
                $(this).text((index + 1))
                exTotalAmount += Number($(this).closest('tr').find('.ex-subtotal').text())
            })
            $('.ex-total-amount').val(exTotalAmount)
            calculateTotal()
        }

        function SerialItem(object) {
            let tr = $(object).closest('tr');
            let serial = Number($(object).parent().text());
            let returable_qty = Number(tr.find('.returnable-qty').text());
            let return_qty = Number(tr.find('.product-quantity').val()) | 0;
            let price = Number(tr.find('.product-price').val());
            let serial_items = tr.find('.product-serial').length
            let new_items = serial_items - 1
            let subtotal = Number(new_items * price);
            tr.find('.subtotal').text(subtotal);

            calculateSubtotal()

            let quantity_row = $(object).closest('tr').find('.quantity-td')
            quantity_row.html(`<input type="hidden" name="quantities[]" value="${new_items}">${new_items}`)

            $(`#serial${serial}`).remove()
            $(object).parent().remove();
        }

        function calculateSubtotal() {
            let totalAmount = 0
            $('.item-serial').each(function (index) {
                totalAmount += Number($(this).closest('tr').find('.subtotal').text())
            })
            $('.total-amount').val(totalAmount)

            let exTotalAmount = 0
            $('.ex-item-serial').each(function (index) {
                exTotalAmount += Number($(this).closest('tr').find('.ex-subtotal').text())
            })
            $('.ex-total-amount').val(exTotalAmount)

            calculateTotal()
        }

        function removeRow(object) {
            $(object).closest('tr').remove();
            calculateSubtotal()
        }

        function removeExRow(object) {
            $(object).closest('tr').remove();
            calculateSubtotal()
        }

    </script>
@endsection