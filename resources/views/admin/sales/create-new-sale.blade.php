@php
    $productTax                     = optional($settings->where('title', 'Product Tax')->first())->options == 'yes';
    $customerPoint                  = optional($settings->where('title', 'Customer Point')->first())->options == 'yes';
    $hasProductRak                  = optional($settings->where('title', 'Product Rak In Product')->first())->options == 'yes';
    $productDiscount                = optional($settings->where('title', 'Product Discount')->first())->options == 'yes';
    $is_default_pos_print           = optional($settings->where('title', 'Sale Default Printing')->first())->options == 'pos-print';
    $has_product_wise_discount      = true; // optional($settings->where('title', 'Product Discount')->first())->options == 'yes';
    $has_additional_item_field      = optional($settings->where('title', 'Need Additional Item Id Field')->first())->options == 'need-additional-item-id-field';
    $allowSalesAvailableQuantity    = optional($settings->where('title', 'Allow Sales When Stock Not Available')->first())->options == 'yes';
    $default_delivery_payer         = optional($settings->where('title', 'Default Delivery Charge Payer')->first())->options ?? 'company';
@endphp

@extends('admin.master')

@section('title', ' - Sale')

@push('style')
<style>
    .form-group {
        margin-bottom: 8px;
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

    .is-invalid{
        border-color: #f78484cc !important;
    }
    .is-valid{
        border-color: #38c18287 !important;
    }

    .profit-column {
        margin-top: -22px !important;
        padding-right: 5px !important;
        text-align: right !important;
        width: 100% !important;
        font-size: 11px !important;
    }

    .select2-container--default .select2-results__option[aria-disabled=true] {
        display: none !important;
    }

    .input-group-text {
        width: 110px !important;
        padding-left: 5px;
    }

    .input-group-prepend, .height-30 {
        height: 30px !important;
    }

    /* Stock badge */
    .stock-badge {
        display: inline-block;
        font-size: 10px;
        font-weight: bold;
        padding: 1px 5px;
        border-radius: 3px;
        margin-top: 2px;
    }
    .stock-badge.in-stock  { background: #d4edda; color: #155724; }
    .stock-badge.low-stock { background: #fff3cd; color: #856404; }
    .stock-badge.no-stock  { background: #f8d7da; color: #721c24; }

    /* Toast notification */
    #stock-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
        min-width: 280px;
        display: none;
    }
</style>
@endpush

@section('content')
    @include('admin.people.customers.quickadd', ['errors' => $errors])

    <!-- Toast Notification -->
    <div id="stock-toast" class="alert alert-danger alert-dismissible shadow" role="alert">
        <strong><i class="fa fa-exclamation-triangle"></i> Stock সতর্কতা!</strong>
        <span id="stock-toast-msg"></span>
        <button type="button" onclick="$('#stock-toast').fadeOut()" style="background:none;border:none;float:right;font-size:16px;cursor:pointer;">&times;</button>
    </div>

    <main class="app-content">
        <form class="form-horizontal" id="saleForm" name="saleForm" method="POST" action="{{ route('sales.new.sale.store') }}">
            @csrf
            <div class="row form-screen">
                <div class="col-md-9">
                    @include('partials._alert_message')

                    <div class="bs-component">
                        <div class="card">
                            <h4 class="card-header text-white" style="background-color: #00728c;">
                                <span class="float-left"><i class="fa fa-plus"></i> Supplier Wise Sale</span>
                                <span class="float-right">
                                    
                                    @if (\App\SoftwareSetting::where('title', 'Hold Option In Sale')->where('options', 'yes')->where('fk_company_id', auth()->user()->fk_company_id)->count() > 0)
                                    <a href="#" onclick="showHoldListModal()" class="btn btn-warning btn-sm mr-2" style="padding: 2px 8px; font-size: 12px; font-weight: bold;"><i class="fa fa-list"></i> Hold List <span class="badge badge-light hold-count-badge" id="hold-count-badge">0</span></a>
                                    @endif
                                    <a href="#" onclick="toggleFullScreen()" title="Full Screen" style="color:#fff;"><i class="fa fa-expand"></i></a>
                                </span>
                            </h4>
                            <input type="hidden" name="sale_type" value="">
                            <div class="card-body">
                                @include('admin.sales.includes.filter-heading-table')
                                @include('admin.sales.includes.prroduct-entry-panel')
                                <div class='row'>
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered" id="table">
                                                <thead>
                                                    <tr>
                                                        <th width="3%">Sl.</th>
                                                        <th>Product Name</th>
                                                        <th>Product Code</th>
                                                        @if($has_additional_item_field)
                                                            <th style="width: 10%;">Item Id</th>
                                                        @endif
                                                        <th style="width: 8%;" class="text-center">Avail. Qty</th>
                                                        <th style="width: 10%;" class="text-center">Quantity</th>
                                                        <th class="text-center" width="12%">Price</th>
                                                        @if($has_product_wise_discount)
                                                            <th style="width: 10%;" class="text-center">Dis.(%)</th>
                                                        @endif
                                                        <th class="text-center" width="12%">Subtotal</th>
                                                        <th class="text-right" width="10%">Profit</th>
                                                        <th width="1%"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="item-table-body">
                                                    @if(old('product_names'))
                                                        @foreach(old('product_names') as $productId => $productName)
                                                            @php
                                                                $qty       = old('quantities')[$productId] ?? 0;
                                                                $price     = old('unit_prices')[$productId] ?? 0;
                                                                $subtotal  = old('product_sub_total')[$productId] ?? 0;
                                                                $tax       = old('product_taxes')[$productId] ?? 0;
                                                                $profit    = old('product_profit')[$productId] ?? 0;
                                                                $cost      = old('product_costs')[$productId] ?? 0;
                                                                $rakName   = old('product_rak_names')[$productId] ?? '';
                                                            @endphp
                                                            <tr data-product-id="{{ $productId }}">
                                                                <td><span class="item-serial-counter"></span></td>
                                                                <td>
                                                                    {{ $productName }}
                                                                    <input type="hidden" name="product_ids[{{ $productId }}]" value="{{ $productId }}">
                                                                    <input type="hidden" name="product_names[{{ $productId }}]" class="product-name" value="{{ $productName }}" autocomplete="off">
                                                                    <input type="hidden" name="profit_columns[{{ $productId }}]" class="profit-column-input" value="{{ $profit }}">
                                                                    <input type="hidden" name="product_taxes[{{ $productId }}]" class="tax-percent" value="{{ $tax }}">
                                                                    <input type="hidden" name="product_rak_names[{{ $productId }}]" class="product-rak-input" value="{{ $rakName }}">
                                                                    <input type="hidden" name="has_serials[{{ $productId }}]" value="">
                                                                    <input type="hidden" name="product_discounts[{{ $productId }}]" value="0">
                                                                    <p class="product-rak-text" style="color: #156984; margin-top: -14px; margin-bottom: 0; font-size: 11px">{{ $rakName }}</p>
                                                                    <p class="item-serial-lists" style="margin-top: 20px !important; margin-bottom: 0"></p>
                                                                </td>
                                                                <td></td>
                                                                @if($has_additional_item_field)
                                                                    <td><input type="text" name="product_item_ids[{{ $productId }}]" value="{{ old('product_item_ids')[$productId] ?? '' }}" class="form-control" placeholder="123"></td>
                                                                @endif
                                                                <td class="text-center">
                                                                    <span class="available-stock-display" style="font-weight:bold;color:#007bff;">-</span>
                                                                    <input type="hidden" class="available-stock" value="0">
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="quantities[{{ $productId }}]" onkeyup="itemQuantityKeyUp(this)" class="form-control text-center item-quantity changesNo" value="{{ $qty }}" autocomplete="off" onkeypress="return IsNumeric(event);">
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="unit_prices[{{ $productId }}]" class="form-control unit-price changesNo" autocomplete="off" onkeyup="priceEnter(event, this)" value="{{ $price }}" onkeypress="return IsNumeric(event);">
                                                                </td>
                                                                @if($has_product_wise_discount)
                                                                <td>
                                                                    <input type="text" name="product_discounts[{{ $productId }}]" class="form-control product-discount-percent changesNo" autocomplete="off" value="0" onkeyup="priceEnter(event, this)" onkeypress="return IsNumeric(event);">
                                                                </td>
                                                                @else
                                                                <input type="hidden" name="product_discounts[{{ $productId }}]" class="product-discount-percent" value="0">
                                                                @endif
                                                                <td>
                                                                    <input type="text" name="product_sub_total[{{ $productId }}]" class="form-control subtotal" value="{{ $subtotal }}" readonly style="height:30px;">
                                                                    <input type="hidden" class="product-cost" name="product_costs[{{ $productId }}]" value="{{ $cost }}">
                                                                    <input type="hidden" name="product_profit[{{ $productId }}]" class="product-profit" value="{{ $profit }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control text-right profit-column-display bg-light" value="{{ $profit }}" readonly style="height:30px;">
                                                                    <input type="hidden" name="profit_columns[{{ $productId }}]" class="profit-column-input" value="{{ $profit }}">
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button" class="btn btn-danger btn-sm" title="Delete This Row" onclick="removeRow(this)">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('admin.sales.includes.right-side-total-calculations')
            </div>
        </form>
    </main>

<!-- Hold List Modal -->
    <div class="modal fade" id="holdListModal" tabindex="-1" role="dialog" aria-labelledby="holdListModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="holdListModalLabel"><i class="fa fa-list"></i> Held Sales List</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>Sl</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Time</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="hold-list-tbody">
                            <!-- JS will populate this -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <input type="hidden" class="customer_due_limit" value="{{ optional($customers->where('is_default', 1)->first())->due_limit ?? 999999999999999 }}">
    <input type="hidden" class="check-has-additional-item-field" value="{{ $has_additional_item_field ? 'yes' : 'no' }}">
    <input type="hidden" class="check-allow-sale-available-quantity" value="{{ $allowSalesAvailableQuantity ? 'yes' : 'no' }}">
    <input type="hidden" class="has-product-rak" value="{{ $hasProductRak ? 'yes' : 'no' }}">
    <input type="hidden" class="has-product-discount" value="yes">
    <input type="hidden" class="customer-point-type">
    <input type="hidden" class="customer-amount-of">
    <input type="hidden" class="customer-amount">
@endsection

@section('js')
    <script src="{{ asset('assets/admin/jq/customerQuickAdd.js?v=') . time() }}"></script>
    <script src="{{ asset('jq/loadDetails.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/custom_js/toggle-full-screen.js') }}"></script>

<script type="text/javascript">

    let isResettingProduct = false;
    let allProducts = null;

    // =============================================
    // STOCK TOAST NOTIFICATION
    // =============================================
    function showStockToast(message) {
        $('#stock-toast-msg').text(' ' + message);
        $('#stock-toast').fadeIn(300);
        setTimeout(function () { $('#stock-toast').fadeOut(500); }, 3500);
    }

    // =============================================
    // DOCUMENT READY
    // =============================================
    $(document).ready(function () {

        $('.select2').select2();
        let savedAccountId = localStorage.getItem('default_select_account_id');
        if (savedAccountId) {
            $('.select-account').val(savedAccountId).trigger('change');
        }
        allProducts = $('.select-product').find('option').clone();

        if ($('.select-currier').val() == '') {
            $('.currier-amount').hide();
        } else {
            $('.currier-amount').show();
        }

        $('.item-table-body tr').each(function () { calculateRowValues($(this)); });
        calculateTotal();

        // Trigger change to load default customer area
        if ($('.selected-customer').val()) {
            let initialCustomer = $('.selected-customer').find('option:selected');
            $('.customer-area-input').val(initialCustomer.attr('data-customer-area') || '');
        }

        // KEYBOARD NAVIGATION
        $('.selected-customer').on('select2:select', function () {
            setTimeout(function () { $('#supplier_id').select2('open'); }, 100);
        });
        $('#supplier_id').on('select2:select', function () {
            setTimeout(function () { $('.select-product').select2('open'); }, 150);
        });
        $('.select-product').on('select2:select', function () {
            setTimeout(function () {
                if (!$('.select-quantity').prop('readonly')) {
                    $('.select-quantity').focus().select();
                } else {
                    $('.select-unit-price').focus().select();
                }
            }, 100);
        });

        // SUPPLIER CHANGE → PRODUCT FILTER
        $('#supplier_id').on('change', function () {
            let supplierId = $(this).val();

            isResettingProduct = true;
            $('.select-product').val('').trigger('change');
            isResettingProduct = false;
            $('.select-available-quantity').val('');
            $('.select-quantity').val('');
            $('.select-unit-price').val('');
            $('.select-unit-cost').val('');
            $('#qty-stock-warning').remove();

            if ($('.select-product').data('select2')) {
                $('.select-product').select2('destroy');
            }
            $('.select-product').empty();

            if (allProducts) {
                allProducts.each(function () {
                    let optionSupplierId = $(this).attr('data-supplier-id');
                    let optionValue      = $(this).val();
                    if (optionValue === '' || !supplierId || optionSupplierId == supplierId) {
                        $('.select-product').append($(this).clone());
                    }
                });
            }

            $('.select-product').select2({ width: '100%', placeholder: 'Select Product' });

            let customerId = $('.selected-customer').val();
            if (supplierId) {
                $.ajax({
                    url: '/search-product-by-supplier-customer/' + supplierId,
                    type: 'GET',
                    data: { customer_id: customerId },
                    success: function (res) {
                        $('.advanced-payment').val(parseFloat(res.customer_advance || 0).toFixed(2));
                        $('.previous-due').val(parseFloat(res.customer_due || 0).toFixed(2));
                        calculateTotal();
                    }
                });
            }

            setTimeout(function () { $('.select-product').select2('open'); }, 200);
        });

        // CUSTOMER CHANGE
        $('.selected-customer').on('change', function () {
            let customerId = $(this).val();
            let customer   = $(this).find('option:selected');
            $('.customer_due_limit').val(customer.attr('data-due-limit') || 999999999);
            $('.customer-area-input').val(customer.attr('data-customer-area') || '');

            let supplierId = $('#supplier_id').val();

            if (customerId) {
                if (supplierId) {
                    $.ajax({
                        url: '/get-customer-supplier-balance',
                        type: 'GET',
                        data: { customer_id: customerId, supplier_id: supplierId },
                        success: function (response) {
                            if (response.previous_due !== undefined && response.previous_due > 0) {
                                $('.previous-due').val(parseFloat(response.previous_due).toFixed(2));
                                $('.advanced-payment').val('0');
                            } else if (response.advanced_payment !== undefined && response.advanced_payment > 0) {
                                $('.advanced-payment').val(parseFloat(response.advanced_payment).toFixed(2));
                                $('.previous-due').val('0');
                            } else {
                                $('.previous-due').val('0');
                                $('.advanced-payment').val('0');
                            }
                            calculateTotal();
                        },
                        error: function () {
                            let currentBalance = parseFloat(customer.attr('data-current-balance')) || 0;
                            if (currentBalance < 0) {
                                $('.advanced-payment').val(Math.abs(currentBalance).toFixed(2));
                                $('.previous-due').val('0');
                            } else {
                                $('.previous-due').val(currentBalance.toFixed(2));
                                $('.advanced-payment').val('0');
                            }
                            calculateTotal();
                        }
                    });
                } else {
                    $.get('/get-customer-balance/' + customerId, function (response) {
                        if (response.previous_due) {
                            $('.previous-due').val(parseFloat(response.previous_due).toFixed(2));
                            $('.advanced-payment').val('0');
                        } else if (response.advanced_payment) {
                            $('.advanced-payment').val(parseFloat(response.advanced_payment).toFixed(2));
                            $('.previous-due').val('0');
                        } else {
                            $('.previous-due').val('0');
                            $('.advanced-payment').val('0');
                        }
                        calculateTotal();
                    }).fail(function () {
                        let currentBalance = parseFloat(customer.attr('data-current-balance')) || 0;
                        if (currentBalance < 0) {
                            $('.advanced-payment').val(Math.abs(currentBalance).toFixed(2));
                            $('.previous-due').val('0');
                        } else {
                            $('.previous-due').val(currentBalance.toFixed(2));
                            $('.advanced-payment').val('0');
                        }
                        calculateTotal();
                    });
                }
            } else {
                $('.previous-due').val('0');
                $('.advanced-payment').val('0');
                calculateTotal();
            }
        });

        // PRODUCT SELECT → AUTO FILL
        $('.select-product').on('change', function () {
            if (isResettingProduct) return;

            $('#qty-stock-warning').remove();

            let productId = $(this).val();
            if (!productId) {
                $('.select-unit-price').val('');
                $('.select-unit-cost').val('');
                $('.select-available-quantity').val('');
                $('.select-quantity').val('');
                return;
            }

            let selected     = $(this).find('option:selected');
            let defaultPrice = parseFloat(selected.attr('data-product-price')) || 0;
            let productCost  = parseFloat(selected.attr('data-product-cost')) || 0;
            let availableQty = parseFloat(selected.attr('data-available-quantity')) || 0;
            let hasSerial    = selected.attr('data-has-serial');

            $('.select-unit-price').val(defaultPrice);
            $('.select-unit-cost').val(productCost);
            $('.select-available-quantity').val(availableQty);

            if (availableQty <= 0) {
                $('<div id="qty-stock-warning" style="color:red;font-weight:bold;margin-top:4px;">⚠ এই product এর stock নেই! Sale করা যাবে না।</div>')
                    .insertAfter('.select-quantity');
            }

            if (hasSerial == 1) {
                $('.select-quantity').val(0).prop('readonly', true);
                $('.product-serial-section').show();
                $('.select-unit-price').focus();
            } else {
                $('.select-quantity').prop('readonly', false).val('').focus();
                $('.product-serial-section').hide();
            }

            let customerId = $('[name="fk_customer_id"]').val();
            if (customerId) {
                $.ajax({
                    url: '/check-customer-price',
                    type: 'GET',
                    data: { customerId: customerId, productId: productId },
                    success: function (response) {
                        if (response.price !== null && typeof response.price !== 'undefined') {
                            $('.select-unit-price').val(response.price);
                        }
                    }
                });
            }
        });

        // QUANTITY KEYUP → STOCK WARNING
        $('.select-quantity').on('keyup', function () {
            let availableQty = parseFloat($('.select-available-quantity').val()) || 0;
            let enteredQty   = parseFloat($(this).val()) || 0;

            $('#qty-stock-warning').remove();

            if (availableQty <= 0) {
                $('<div id="qty-stock-warning" style="color:red;font-weight:bold;margin-top:4px;">⚠ এই product এর stock নেই! Sale করা যাবে না।</div>')
                    .insertAfter($(this));
            } else if (enteredQty > availableQty) {
                $('<div id="qty-stock-warning" style="color:red;font-weight:bold;margin-top:4px;">⚠ Stock নেই! Available: ' + availableQty + '</div>')
                    .insertAfter($(this));
            }
        });

        // QUANTITY ENTER → MOVE TO PRICE
        $('.select-quantity').on('keypress', function (event) {
            if (event.which == 13) {
                event.preventDefault();
                let quantity     = parseFloat($(this).val() || 0);
                let availableQty = parseFloat($('.select-available-quantity').val()) || 0;

                if (quantity <= 0) {
                    isResettingProduct = true;
                    $('.select-product').val('').trigger('change');
                    isResettingProduct = false;
                    $('.select-quantity').val('');
                    $('.select-unit-price').val('');
                    $('.select-available-quantity').val('');
                    $('#qty-stock-warning').remove();
                    $('.select-product').select2('open');
                } else if (quantity > availableQty) {
                    // Block — warning already showing
                } else {
                    $('.select-unit-price').focus().select();
                }
            }
        });

        // PRICE ENTER → INSERT ROW
        $('.select-unit-price').on('keypress', function (event) {
            if (event.which == 13) { event.preventDefault(); insertNewItem(); }
        });

        // RIGHT SIDE NAVIGATION
        $('.discount-percent').on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); $('.discount-amount').focus(); } });
        $('.discount-amount').on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); $('.special-discount-percent').focus(); } });
        $('.special-discount-percent').on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); $('.special-discount-amount').focus(); } });
        $('.special-discount-amount').on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); $('.delivery-charge').focus(); } });
        $('.delivery-charge').on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); $('.deliverer').focus(); } });
        $('.deliverer').on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); $('.receive-amount').focus().select(); } });
        $('.receive-amount').on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); $('.save-btn').click(); } });

        // DISCOUNT KEYUP
        $(document).on('keyup', '.discount-percent', function () { if (parseFloat($(this).val()) > 0) $('.discount-amount').val(0); calculateTotal(); });
        $(document).on('keyup', '.discount-amount', function () { if (parseFloat($(this).val()) > 0) $('.discount-percent').val(0); calculateTotal(); });
        $(document).on('keyup', '.special-discount-percent', function () { if (parseFloat($(this).val()) > 0) $('.special-discount-amount').val(0); calculateTotal(); });
        $(document).on('keyup', '.special-discount-amount', function () { if (parseFloat($(this).val()) > 0) $('.special-discount-percent').val(0); calculateTotal(); });
        $(document).on('keyup', '.product-discount-percent', function () { let row = $(this).closest('tr'); if (parseFloat($(this).val()) > 0) row.find('.product-discount-flat').val(0); calculateRowValues(row); calculateTotal(); });
        $(document).on('keyup', '.product-discount-flat', function () { let row = $(this).closest('tr'); if (parseFloat($(this).val()) > 0) row.find('.product-discount-percent').val(0); calculateRowValues(row); calculateTotal(); });
        $(document).on('keyup', '.changesNo', function () { let row = $(this).closest('tr'); calculateRowValues(row); calculateTotal(); });

        // TABLE ROW STOCK CHECK
        if ($('.check-allow-sale-available-quantity').val() != 'yes') {
            $(document).on('keyup', '.item-quantity', function () {
                let quantity = parseFloat($(this).val()) || 0;
                let stock    = parseFloat($(this).closest('tr').find('.available-stock').val()) || 0;
                let row      = $(this).closest('tr');
                row.find('.stock-warning').remove();
                if (quantity > stock) {
                    row.find('td:first').append('<div class="stock-warning" style="color:red;font-size:11px;font-weight:bold;">⚠ Stock: ' + stock + '</div>');
                }
            });
        }

        $('.receive-amount, .input-currier-amount, .delivery-charge').on('keyup', function () { calculateTotal(); });
        $(document).on('change', '.delivery-payer', function () { calculateTotal(); });

        $('.select-account').on('change', function () {
            let accountId = $(this).val();
            localStorage.setItem('default_select_account_id', accountId);
            if (accountId == '') {
                $('.account-balance').val('');
            } else {
                $('.account-balance').val($(this).find('option:selected').data('total-amount'));
            }
        });

        $('.select-currier').on('change', function () {
            if ($(this).val() == '') { $('.currier-amount').hide(); } else { $('.currier-amount').show(); }
        });

        // =============================================
        // BARCODE SCANNER — AVAILABLE QTY SHOW + BLOCK
        // =============================================
        $(document).on('keypress', '#barcode_input', function (event) {
            if (event.key !== 'Enter') return;
            event.preventDefault();

            let barcode = $(this).val().trim();
            if (barcode === '') return;

            let selectedOption = $('.select-product option').filter(function () {
                return $(this).attr('data-product-code') === barcode || $(this).attr('data-barcode') === barcode;
            });

            if (selectedOption.length === 0) {
                showStockToast('Product পাওয়া যায়নি: ' + barcode);
                $(this).val('');
                return;
            }

            let product = {
                id:               selectedOption.val(),
                name:             selectedOption.attr('data-product-name'),
                product_code:     selectedOption.attr('data-product-code'),
                customer_price:   parseFloat(selectedOption.attr('data-product-price')) || 0,
                product_cost:     parseFloat(selectedOption.attr('data-product-cost')) || 0,
                retail_quantity:  parseFloat(selectedOption.attr('data-available-quantity')) || 0,
                tax:              parseFloat(selectedOption.attr('data-tax-percent')) || 0,
                has_serial:       selectedOption.attr('data-has-serial'),
                discount:         0,
                product_rak_name: ''
            };

            // Stock 0 হলে block
            if (product.retail_quantity <= 0) {
                showStockToast(product.name + ' — Stock নেই! Sale করা যাবে না।');
                $(this).val('');
                return;
            }

            let existingRow = $('.item-table-body tr').filter(function () {
                return $(this).find('.product-id').val() == product.id;
            });

            if (existingRow.length > 0) {
                let qtyInput    = existingRow.find('.item-quantity');
                let currentQty  = parseFloat(qtyInput.val()) || 0;
                let availStock  = parseFloat(existingRow.find('.available-stock').val()) || product.retail_quantity;

                if ((currentQty + 1) > availStock) {
                    showStockToast(product.name + ' — Stock শেষ! Available: ' + availStock);
                } else {
                    qtyInput.val(currentQty + 1);
                    existingRow.css('background-color', '#d4edda');
                    setTimeout(() => existingRow.css('background-color', ''), 600);
                    // Update available qty display
                    existingRow.find('.available-stock-display').text(availStock);
                    calculateRowValues(existingRow);
                    calculateTotal();
                }
            } else {
                let newRow = addNewRow(product.product_code);
                setProductInfo(newRow, product);
                calculateTotal();
            }

            $(this).val('');
            $(this).focus();
        });

    }); // END document.ready

    // =============================================
    // KEYBOARD SHORTCUTS
    // =============================================
    $(document).on('keydown', function (event) {
        if (event.key === 'F1') { event.preventDefault(); $('.selected-customer').select2('open'); }
        if (event.key === 'F2') { event.preventDefault(); $('#barcode_input').focus(); }
        if (event.key === 'F3') {
            event.preventDefault();
            if (!$('#supplier_id').val()) { alert('Please select a Supplier first!'); $('#supplier_id').select2('open'); }
            else { $('.select-product').select2('open'); }
        }
        if (event.key === 'F8') { event.preventDefault(); $('.receive-amount').focus().select(); }
        if (event.key === 'F10' || (event.ctrlKey && event.key === 'Enter')) { event.preventDefault(); $('.save-btn').click(); }
    });

    // =============================================
    // INSERT NEW ITEM
    // =============================================
    function insertNewItem() {
        let productSelect = $('.select-product');
        let productId     = productSelect.val();
        if (!productId) return false;

        if (!$('#supplier_id').val()) {
            alert('Please select a Supplier first!');
            $('#supplier_id').select2('open');
            return false;
        }

        let selected     = productSelect.find('option:selected');
        let productName  = selected.attr('data-product-name');
        let productCode  = selected.attr('data-product-code');
        let productPrice = parseFloat($('.select-unit-price').val() || selected.attr('data-product-price') || 0);
        let quantity     = parseFloat($('.select-quantity').val() || 0);
        let availableQty = parseFloat($('.select-available-quantity').val()) || 0;
        let taxProduct   = selected.attr('data-tax-percent') || 0;
        let productCost  = selected.attr('data-product-cost') || 0;
        let hasSerial    = selected.attr('data-has-serial') || 0;

        if (quantity <= 0) {
            isResettingProduct = true;
            productSelect.val('').trigger('change');
            isResettingProduct = false;
            $('.select-available-quantity').val('');
            $('.select-quantity').val('');
            $('.select-unit-price').val('');
            $('#qty-stock-warning').remove();
            productSelect.select2('open');
            return false;
        }

        if (quantity > availableQty) {
            if (availableQty <= 0) {
                showStockToast('এই product এর stock নেই! Sale করা যাবে না।');
            } else {
                showStockToast('Stock নেই! Available quantity: ' + availableQty);
            }
            $('.select-quantity').focus().select();
            return false;
        }

        let existingRow = $(`.item-table-body tr[data-product-id="${productId}"]`);
        if (existingRow.length > 0) {
            let currentQty = parseFloat(existingRow.find('.item-quantity').val() || 0);
            let newQty     = currentQty + quantity;
            let existStock = parseFloat(existingRow.find('.available-stock').val()) || 0;

            if (newQty > existStock) {
                showStockToast('Stock নেই! Available: ' + existStock);
                return false;
            }

            existingRow.find('.item-quantity').val(newQty).trigger('keyup');
        } else {
            let additionalField = '';
            if ($('.check-has-additional-item-field').val() == 'yes') {
                additionalField = `<td><input type="text" name="product_item_ids[${productId}]" value="" class="form-control" placeholder="123"></td>`;
            }
            let subtotal = (quantity * productPrice).toFixed(2);
            let newRow = `<tr data-product-id="${productId}">
                <td><span class="item-serial-counter"></span></td>
                <td>
                    ${productName}
                    <input type="hidden" name="product_ids[${productId}]" value="${productId}">
                    <input type="hidden" name="product_names[${productId}]" class="product-name" value="${productName}" autocomplete="off">
                    <input type="hidden" name="product_taxes[${productId}]" class="tax-percent" value="${taxProduct}">
                    <input type="hidden" name="product_rak_names[${productId}]" class="product-rak-input" value="">
                    <input type="hidden" name="has_serials[${productId}]" value="${hasSerial}">
                    <input type="hidden" name="product_discounts[${productId}]" value="0">
                    <p class="product-rak-text" style="color:#156984;margin-top:-14px;margin-bottom:0;font-size:11px"></p>
                    <p class="item-serial-lists" style="margin-top:20px!important;margin-bottom:0"></p>
                </td>
                <td>${productCode}</td>
                <td class="text-center">
                    <span class="available-stock-display" style="font-weight:bold;color:#007bff;">${availableQty}</span>
                    <input type="hidden" class="available-stock" value="${availableQty}">
                </td>
                ${additionalField}
                <td><input type="text" name="quantities[${productId}]" onkeyup="itemQuantityKeyUp(this)" class="form-control text-center item-quantity changesNo" value="${quantity}" autocomplete="off" onkeypress="return IsNumeric(event);"></td>
                <td><input type="text" name="unit_prices[${productId}]" class="form-control unit-price changesNo" autocomplete="off" onkeyup="priceEnter(event, this)" value="${productPrice}" onkeypress="return IsNumeric(event);"></td>
                ${ $('.has-product-discount').val() == 'yes' ? '<td><input type="text" name="product_discounts['+productId+']" class="form-control product-discount-percent changesNo" autocomplete="off" value="0" onkeyup="priceEnter(event, this)" onkeypress="return IsNumeric(event);"></td>' : '<input type="hidden" name="product_discounts['+productId+']" class="product-discount-percent" value="0">' }
                <td>
                    <input type="text" name="product_sub_total[${productId}]" class="form-control subtotal" value="${subtotal}" readonly style="height:30px;">
                    <input type="hidden" class="product-cost" name="product_costs[${productId}]" value="${productCost}">
                    <input type="hidden" name="product_profit[${productId}]" class="product-profit" value="0">
                </td>
                <td>
                    <input type="text" class="form-control text-right profit-column-display bg-light" value="0.00" readonly style="height:30px;">
                    <input type="hidden" name="profit_columns[${productId}]" class="profit-column-input" value="0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa fa-trash"></i></button>
                </td>
            </tr>`;
            $('.item-table-body').append(newRow);
        }

        setItemSerial();
        $('.item-table-body tr').each(function () { calculateRowValues($(this)); });
        calculateTotal();

        isResettingProduct = true;
        productSelect.val('').trigger('change');
        isResettingProduct = false;
        $('.select-available-quantity').val('');
        $('.select-quantity').val('');
        $('.select-unit-price').val('');
        $('.select-unit-cost').val('');
        $('#qty-stock-warning').remove();

        productSelect.select2('open');
    }

    // =============================================
    // ROW CALCULATIONS
    // =============================================
    function calculateRowValues(row) {
        let quantity         = parseFloat(row.find('.item-quantity').val()) || 0;
        let price            = parseFloat(row.find('.unit-price').val()) || 0;
        let discount_percent = parseFloat(row.find('.product-discount-percent').val()) || 0;
        let discount_flat    = parseFloat(row.find('.product-discount-flat').val()) || 0;
        let cost             = parseFloat(row.find('.product-cost').val()) || 0;

        let total_price    = price * quantity;
        let total_discount = discount_flat + (total_price * discount_percent / 100);
        let subtotal       = total_price - total_discount;
        let profit         = ((price - cost) * quantity) - total_discount;

        row.find('.subtotal').val(subtotal.toFixed(2));
        row.find('.product-profit').val(profit.toFixed(2));
        row.find('.profit-column-display').val(profit.toFixed(2));
        row.find('.profit-column-input').val(profit.toFixed(2));
    }

    // =============================================
    // TOTAL CALCULATION
    // =============================================
    function calculateTotal() {
        let invoiceTotal = 0;
        let totalProfit  = 0;

        $('.item-table-body tr').each(function () {
            let subtotal = parseFloat($(this).find('.subtotal').val()) || 0;
            let profit   = parseFloat($(this).find('.product-profit').val()) || 0;

            invoiceTotal += subtotal;
            totalProfit  += profit;
        });

        $('.invoice-total').val(invoiceTotal.toFixed(2));

        let discountPercent    = parseFloat($('.discount-percent').val()) || 0;
        let discountFlat       = parseFloat($('.discount-amount').val()) || 0;
        let firstDiscount      = discountFlat + (invoiceTotal * discountPercent / 100);
        let afterFirstDiscount = invoiceTotal - firstDiscount;

        let specialPercent    = parseFloat($('.special-discount-percent').val()) || 0;
        let specialFlat       = parseFloat($('.special-discount-amount').val()) || 0;
        let specialDiscount   = specialFlat + (afterFirstDiscount * specialPercent / 100);
        let afterBothDiscount = afterFirstDiscount - specialDiscount;

        let invoiceTax       = parseFloat($('.invoice-tax').val()) || 0;
        let codAmount        = parseFloat($('.cod-amount').val()) || 0;
        let deliveryCharge   = parseFloat($('.delivery-charge').val()) || 0;
        let deliveryPayer    = $('.delivery-payer').val();
        let customerDelivery = (deliveryPayer === 'customer') ? deliveryCharge : 0;

        let payableAmount = afterBothDiscount + invoiceTax + codAmount + customerDelivery;
        $('.payable-amount').val(payableAmount.toFixed(2));

        totalProfit = totalProfit - firstDiscount - specialDiscount;
        $('#total-profit-amount').val(totalProfit.toFixed(2));
        $('#profit-display').text(totalProfit.toFixed(2));

        let previousDue     = parseFloat($('.previous-due').val()) || 0;
        let advancedPayment = parseFloat($('.advanced-payment').val()) || 0;
        let totalPayable    = payableAmount;
        $('.total-payable-amount').val(totalPayable.toFixed(2));

        let receiveAmount = parseFloat($('.receive-amount').val()) || 0;
        let totalPaid     = receiveAmount + advancedPayment;
        let overallBalance = previousDue + totalPayable;

        if (totalPaid > overallBalance) {
            $('.change-return').val((totalPaid - overallBalance).toFixed(2));
            $('.current-balance').val('0.00');
        } else {
            $('.change-return').val('0.00');
            $('.current-balance').val((overallBalance - totalPaid).toFixed(2));
        }
    }

    // =============================================
    // ADD NEW ROW
    // =============================================
    function addNewRow(productCode = '') {
        let additionalField = '';
        if ($('.check-has-additional-item-field').val() == 'yes') {
            additionalField = `<td><input type="text" name="product_item_ids[]" value="" class="form-control" placeholder="123"></td>`;
        }
        let newRow = `<tr>
            <td><span class="item-serial-counter"></span></td>
            <td>
                <input type="hidden" name="product_names[]" class="product-name">
                <input type="hidden" class="product-cost" name="product_costs[]">
                <input type="hidden" class="product-id" name="product_ids[]">
                <input type="hidden" class="profit-column-input" name="profit_columns[]">
                <input type="hidden" class="product-rak-input" name="product_rak_names[]">
                <input type="hidden" name="product_taxes[]" class="tax-percent">
                <span class="product-name-display"></span>
                <p class="product-rak-text" style="color:#156984;margin-top:-14px;margin-bottom:0;font-size:11px"></p>
            </td>
            <td><span class="product-code-display">${productCode}</span></td>
            ${additionalField}
            <td class="text-center">
                <span class="available-stock-display" style="font-weight:bold;color:#007bff;">0</span>
                <input type="hidden" class="available-stock" value="0">
            </td>
            <td><input type="text" name="quantities[]" onkeyup="itemQuantityKeyUp(this)" class="form-control text-center item-quantity changesNo" autocomplete="off" onkeypress="return IsNumeric(event);"></td>
            <td><input type="text" name="unit_prices[]" class="form-control unit-price changesNo" autocomplete="off" onkeyup="priceEnter(event, this)" onkeypress="return IsNumeric(event);"></td>
            ${ $('.has-product-discount').val() == 'yes' ? '<td><input type="text" name="product_discounts[]" class="form-control product-discount-percent changesNo" autocomplete="off" value="0" onkeyup="priceEnter(event, this)" onkeypress="return IsNumeric(event);"></td>' : '<input type="hidden" name="product_discounts[]" class="product-discount-percent" value="0">' }
            <td>
                <input type="text" name="product_sub_total[]" class="form-control subtotal" readonly>
                <input type="hidden" class="product-cost" name="product_costs[]">
                <input type="hidden" class="product-profit" name="product_profit[]" value="0">
            </td>
            <td>
                <input type="text" class="form-control text-right profit-column-display bg-light" value="0" readonly style="height:30px;">
                <input type="hidden" class="profit-column-input" name="profit_columns[]" value="0">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa fa-trash"></i></button>
            </td>
        </tr>`;
        $('.item-table-body').append(newRow);
        setItemSerial();
        return $('.item-table-body tr:last');
    }

    // =============================================
    // SET PRODUCT INFO — available qty display সহ
    // =============================================
    function setProductInfo(row, product) {
        let price    = product['customer_price'];
        let cost     = product['product_cost'] || 0;
        let qty      = 1;
        let subtotal = price * qty;
        let stock    = product['retail_quantity'] || 0;
        let pid      = product['id'];

        row.attr('data-product-id', pid);
        row.find('.product-id').attr('name', `product_ids[${pid}]`).val(pid);
        row.find('.product-name').attr('name', `product_names[${pid}]`).val(product['name']);
        row.find('.unit-price').attr('name', `unit_prices[${pid}]`).val(price);
        row.find('.available-stock').val(stock);
        row.find('.available-stock-display').text(stock);  
        row.find('.tax-percent').attr('name', `product_taxes[${pid}]`).val(product['tax']);
        row.find('.product-discount-flat').attr('name', `product_discounts[${pid}]`).val(product['discount'] || 0);
        row.find('.product-discount-percent').val(0);
        row.find('.item-quantity').attr('name', `quantities[${pid}]`).val(qty);
        row.find('.subtotal').attr('name', `product_sub_total[${pid}]`).val(subtotal.toFixed(2));
        row.find('.product-cost').attr('name', `product_costs[${pid}]`).val(cost);
        row.find('.product-profit').attr('name', `product_profit[${pid}]`).val((price - cost).toFixed(2));
        row.find('.profit-column-display').val((price - cost).toFixed(2));
        row.find('.profit-column-input').attr('name', `profit_columns[${pid}]`).val((price - cost).toFixed(2));
        row.find('.product-name-display').text(product['name']);
        row.find('.product-code-display').text(product['product_code']);

        if (row.find('.has-serials').length === 0) {
            row.find('td:eq(1)').append(`<input type="hidden" name="has_serials[${pid}]" class="has-serials" value="${product['has_serial'] || 0}">`);
        } else {
            row.find('.has-serials').attr('name', `has_serials[${pid}]`).val(product['has_serial'] || 0);
        }

        if ($('.check-has-additional-item-field').val() == 'yes') {
            row.find('input[placeholder="123"]').attr('name', `product_item_ids[${pid}]`);
        }

        if ($('.has-product-rak').val() == 'yes') {
            row.find('.product-rak-text').text(product['product_rak_name']);
            row.find('.product-rak-input').attr('name', `product_rak_names[${pid}]`).val(product['product_rak_name']);
        } else {
            row.find('.product-rak-input').attr('name', `product_rak_names[${pid}]`).val('');
        }
    }

    function removeRow(el) { $(el).parents('tr').remove(); calculateTotal(); setItemSerial(); }

    function setItemSerial() {
        $('.item-table-body tr').each(function (i) { $(this).find('.item-serial-counter').text(i + 1); });
    }

    function priceEnter(e, object) {
        if (e.keyCode == 13) {
            if ($('.product-discount').is(':visible')) { $('.product-discount').focus(); }
        } else {
            calculateProductProfit(object);
        }
    }

    function itemQuantityKeyUp(object) {
        let row          = $(object).closest('tr');
        let availableQty = parseFloat(row.find('.available-stock').val()) || 0;
        let enteredQty   = parseFloat($(object).val()) || 0;

        row.find('.stock-warning').remove();

        if (enteredQty > availableQty) {
            row.find('td:eq(1)').append('<div class="stock-warning" style="color:red;font-size:11px;font-weight:bold;">⚠ Stock নেই! Available: ' + availableQty + '</div>');
        }

        calculateProductProfit(object);
    }

    function calculateProductProfit(object) {
        let purchasePrice = parseFloat($(object).closest('tr').find('.product-cost').val()) || 0;
        let salePrice     = parseFloat($(object).closest('tr').find('.unit-price').val()) || 0;
        let itemQuantity  = parseFloat($(object).closest('tr').find('.item-quantity').val()) || 0;
        let profit        = ((salePrice - purchasePrice) * itemQuantity).toFixed(2);
        $(object).closest('tr').find('.profit-column').text(profit);
        $(object).closest('tr').find('.profit-column-input').val(profit);
    }

    function saveSaleInvoice() {
        let receiveField = $('.receive-amount');
        let val = receiveField.val();
        if (val && (val.includes('@') || isNaN(val))) { receiveField.val('0'); }

        if (!$('#supplier_id').val()) { alert('Please select a Supplier first!'); $('#supplier_id').select2('open'); return false; }
        if ($('.item-table-body tr').length === 0) { alert('Please add at least one product'); return false; }

        let qtyOk = true;
        $('.item-quantity').each(function () {
            if ($(this).val() == '' || parseFloat($(this).val()) <= 0) { alert('Product quantity cannot be zero or empty'); qtyOk = false; return false; }
        });
        if (!qtyOk) return false;

        if (parseFloat($('.receive-amount').val() || 0) > 0) {
            if ($('.account-info').val() == '') { alert('Please select transaction account'); return false; }
        }
        
        let dueLimit = parseFloat($('.customer_due_limit').val()) || 0;
        let currentBalance = parseFloat($('.current-balance').val()) || 0;
        if (dueLimit < currentBalance) {
            alert('This customer maximum due limit is ' + dueLimit);
            return false;
        }

        $('#saleForm').submit();
    }

    function toggleProfitVisibility() {
        let profitDisplay = $('#profit-display');
        let eyeIcon       = $('#profit-eye-icon');
        if (profitDisplay.is(':visible')) { profitDisplay.hide(); eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash'); }
        else { profitDisplay.show(); eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye'); }
    }

    // =============================================
    // SERIAL SCANNER FUNCTIONS
    // =============================================
    function getProductBySerial(object) {
        if(event.key === 'Enter' || event.keyCode === 13) {
            event.preventDefault();
            let serialVal = $(object).val().trim();
            if(serialVal === '') return;

            $.get('/product/get-multiple-product-by-product-serial', { serials : serialVal }, function(res) {
                if(res.length === 0) {
                    showStockToast('Product পাওয়া যায়নি: ' + serialVal);
                } else {
                    $.each(res, function(index, value){
                        if(value.serial) {
                            setProduct(value);
                        }
                    });
                }
                $(object).val(''); // clear input
            });
        }
    }

    function setProduct(value) {
        if (value.name !== undefined) {
            let serial          = value.serial;
            let productId       = value.id;
            let productName     = value.name;
            let productCode     = value.code;
            let productPrice    = value.price;
            let productTax      = value.tax || 0;
            let productCost     = value.cost || 0;
            let quantity        = 1;
            let serial_count    = 0;

            // Check if serial is already added
            $('.item-serial').each(function(index, item) {
                let currentSerial = $(item).clone().children().remove().end().text().trim();
                if (serial === currentSerial || serial === $(item).text().trim()) {
                    serial_count++;
                    return false;
                }
            });

            if (serial_count === 0) {
                let name = `product_names[${ productId }]`;

                // If product row already exists
                if($(`input[name="${name}"]`).length === 1) {
                    let product_serial = `<span class="item-serial badge badge-primary mr-1" style="font-size: 12px">${ serial }<span class="d-print-none" style="cursor:pointer;margin-left:3px;" onMouseOver="this.style.color='red'" onMouseOut="this.style.color='white'" onClick="SerialItem(this)"><i class="fa fa-times-circle" ></i></span></span>`;
                    $(`input[name="${name}"]`).closest('td').find('.item-serial-lists').append(product_serial);

                    let input_serial = `<input type="hidden" name="product_serials[${productId}][]" value="${serial}">`;
                    $(`input[name="${name}"]`).closest('td').find('.item-serial-lists').append(input_serial);

                    let totalSerialQty = $(`input[name="${name}"]`).closest('td').find('.item-serial').length;
                    
                    let row = $(`input[name="${name}"]`).closest('tr');
                    row.find('.item-quantity').val(totalSerialQty);
                    
                    row.css('background-color', '#d4edda');
                    setTimeout(() => row.css('background-color', ''), 600);

                } else {
                    // Create new row
                    let additionalField = '';
                    if ($('.check-has-additional-item-field').val() == 'yes') {
                        additionalField = `<td><input type="text" name="product_item_ids[${productId}]" value="" class="form-control" placeholder="123"></td>`;
                    }
                    
                    let subtotal = (quantity * productPrice).toFixed(2);
                    let newRow = `<tr data-product-id="${productId}">
                        <td><span class="item-serial-counter"></span></td>
                        <td>
                            ${productName}
                            <input type="hidden" name="product_ids[${productId}]" value="${productId}">
                            <input type="hidden" name="product_names[${productId}]" class="product-name" value="${productName}" autocomplete="off">
                            <input type="hidden" name="profit_columns[${productId}]" class="profit-column-input" value="0">
                            <input type="hidden" name="product_taxes[${productId}]" class="tax-percent" value="${productTax}">
                            <input type="hidden" name="product_rak_names[${productId}]" class="product-rak-input" value="">
                            <input type="hidden" name="has_serials[${productId}]" value="1">
                            <input type="hidden" name="product_discounts[${productId}]" value="0">
                            <p class="product-rak-text" style="color:#156984;margin-top:-14px;margin-bottom:0;font-size:11px"></p>
                            <p class="item-serial-lists" style="margin-top:20px!important;margin-bottom:0">
                                <input type="hidden" name="product_serials[${productId}][]" value="${serial}">
                                <span class="item-serial badge badge-primary mr-1" style="font-size: 12px">${serial}<span class="d-print-none" style="cursor:pointer;margin-left:3px;" onMouseOver="this.style.color='red'" onMouseOut="this.style.color='white'" onClick="SerialItem(this)"><i class="fa fa-times-circle"></i></span></span>
                            </p>
                        </td>
                        <td>${productCode}</td>
                        <td class="text-center">
                            <span class="available-stock-display" style="font-weight:bold;color:#007bff;">-</span>
                            <input type="hidden" class="available-stock" value="1">
                        </td>
                        ${additionalField}
                        <td><input type="text" name="quantities[${productId}]" onkeyup="itemQuantityKeyUp(this)" class="form-control text-center item-quantity changesNo" value="${quantity}" readonly autocomplete="off" onkeypress="return IsNumeric(event);"></td>
                        <td><input type="text" name="unit_prices[${productId}]" class="form-control unit-price changesNo text-right" autocomplete="off" onkeyup="priceEnter(event, this)" value="${productPrice}" onkeypress="return IsNumeric(event);"></td>
                        ${ $('.has-product-discount').val() == 'yes' ? '<td><input type="text" name="product_discounts['+productId+']" class="form-control product-discount-percent changesNo" autocomplete="off" value="0" onkeyup="priceEnter(event, this)" onkeypress="return IsNumeric(event);"></td>' : '<input type="hidden" name="product_discounts['+productId+']" class="product-discount-percent" value="0">' }
                        <td>
                            <input type="text" name="product_sub_total[${productId}]" class="form-control subtotal text-right" value="${subtotal}" readonly style="height:30px;">
                            <input type="hidden" class="product-cost" name="product_costs[${productId}]" value="${productCost}">
                            <input type="hidden" name="product_profit[${productId}]" class="product-profit" value="0">
                        </td>
                        <td>
                            <span class="profit-column">0.00</span>
                            <input type="hidden" name="profit_columns[${productId}]" class="profit-column-input" value="0">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>`;

                    $('.item-table-body').append(newRow);
                }

                setItemSerial();
                $('.item-table-body tr').each(function () { calculateRowValues($(this)); });
                calculateTotal();
            } else {
                showStockToast('This serial is already added!');
            }
        }
    }

    function SerialItem(element) {
        let row = $(element).closest('tr');
        
        let serialText = $(element).parent().clone().children().remove().end().text().trim();
        $(element).closest('.item-serial').remove();
        
        row.find('input[value="' + serialText + '"]').remove();
        
        let totalSerialQty = row.find('.item-serial').length;
        if (totalSerialQty === 0) {
            row.remove();
        } else {
            row.find('.item-quantity').val(totalSerialQty);
        }
        
        $('.item-table-body tr').each(function () { calculateRowValues($(this)); });
        calculateTotal();
        setItemSerial();
    }

    var specialKeys = [8, 46];
    function IsNumeric(e) {
        let keyCode = e.which ? e.which : e.keyCode;
        return ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
    }

    function SerialItem(object) {
        let tr  = $(object).closest('tr');
        let qty = Number(tr.find('.item-quantity').val()) - 1;
        tr.find('.item-quantity').val(qty);
        let price    = Number(tr.find('.unit-price').val());
        let subtotal = qty * price;
        tr.find('.subtotal').val(subtotal);
        $(object).parent().remove();
        calculateTotal();
    }

    function getProductBySerial(object) {
        $.get('/product/get-multiple-product-by-product-serial', { serials: $(object).val() }, function (res) {
            $.each(res, function (index, value) { if (value.serial) { setProduct(value); } });
        });
    }

    function setCustomerPointInfo(res) {
        $('.customer-point-type').val(res.type);
        $('.customer-amount-of').val(res.amount_of);
        $('.customer-amount').val(res.amount);
    }

function holdCurrentSale() {
        if($('.item-table-body tr').length == 0) {
            if(typeof toastr !== 'undefined') { toastr.warning('Cart is empty! Please add products.'); }
            else { alert('Cart is empty!'); }
            return;
        }

        // Keep a snapshot of input values inside the tr since .html() doesn't always capture the current .val()
        $('.item-table-body tr input').each(function() {
            $(this).attr('value', $(this).val());
        });

        let customerName = $('.selected-customer option:selected').text();
        if(!customerName || customerName.trim() === 'Select Customer' || customerName.trim() === '') {
            customerName = 'Walk-in Customer';
        }

        let saleData = {
            id: Date.now(),
            time: new Date().toLocaleTimeString(),
            customer_id: $('.selected-customer').val(),
            customer_name: customerName,
            supplier_id: $('#supplier_id').val() || '',
            marketer_id: $('[name="fk_marketer_id"]').val() || '',
            cart_html: $('.item-table-body').html(),
            
            discount_percent: $('.discount-percent').val() || 0,
            discount_amount: $('.discount-amount').val() || 0,
            special_discount_percent: $('.special-discount-percent').val() || 0,
            special_discount_amount: $('.special-discount-amount').val() || 0,
            delivery_charge: $('.delivery-charge').val() || 0,
            delivery_payer: $('.delivery-payer').val(),
            
            total_payable: $('.total-payable-amount').val() || 0,
            item_count: $('.item-table-body tr').length
        };
        
        let heldSales = JSON.parse(localStorage.getItem('held_sales_supplier')) || [];
        heldSales.push(saleData);
        localStorage.setItem('held_sales_supplier', JSON.stringify(heldSales));
        
        if(typeof toastr !== 'undefined') { toastr.success('Sale Held Successfully!'); }
        else { alert('Sale Held Successfully!'); }

        updateHoldBadge();
        setTimeout(() => { window.location.reload(); }, 1000);
    }

    function showHoldListModal() {
        let heldSales = JSON.parse(localStorage.getItem('held_sales_supplier')) || [];
        let html = '';
        if(heldSales.length == 0) {
            html = '<tr><td colspan="6" class="text-center font-weight-bold text-danger">No Held Sales Found</td></tr>';
        } else {
            heldSales.forEach((sale, i) => {
                html += `
                <tr>
                    <td>${i+1}</td>
                    <td>${sale.customer_name}</td>
                    <td class="text-center">${sale.item_count}</td>
                    <td class="text-right font-weight-bold">৳ ${sale.total_payable}</td>
                    <td class="text-center">${sale.time}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-success" onclick="resumeHeldSale(${i})"><i class="fa fa-hand-o-right"></i> Resume</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteHeldSale(${i})"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>`;
            });
        }
        $('#hold-list-tbody').html(html);
        $('#holdListModal').modal('show');
    }

    function deleteHeldSale(index) {
        if(!confirm('Are you sure you want to delete this held sale?')) return;
        let heldSales = JSON.parse(localStorage.getItem('held_sales_supplier')) || [];
        heldSales.splice(index, 1);
        localStorage.setItem('held_sales_supplier', JSON.stringify(heldSales));
        showHoldListModal();
        updateHoldBadge();
    }

    function resumeHeldSale(index) {
        let heldSales = JSON.parse(localStorage.getItem('held_sales_supplier')) || [];
        let sale = heldSales[index];
        if(!sale) return;
        
        $('.item-table-body').html(sale.cart_html);
        
        if(sale.customer_id) {
            $('.selected-customer').val(sale.customer_id).trigger('change');
        }
        if(sale.supplier_id) {
            $('#supplier_id').val(sale.supplier_id).trigger('change');
        }
        if(sale.marketer_id) {
            $('[name="fk_marketer_id"]').val(sale.marketer_id).trigger('change');
        }
        $('.discount-percent').val(sale.discount_percent);
        $('.discount-amount').val(sale.discount_amount);
        $('.special-discount-percent').val(sale.special_discount_percent);
        $('.special-discount-amount').val(sale.special_discount_amount);
        $('.delivery-charge').val(sale.delivery_charge);
        $('.delivery-payer').val(sale.delivery_payer).trigger('change');
        
        // Recalculate
        $('.item-table-body tr').each(function () { 
            if(typeof calculateRowValues === 'function') calculateRowValues($(this)); 
        });
        if(typeof calculateTotal === 'function') calculateTotal();
        if(typeof setItemSerial === 'function') setItemSerial();
        
        // Remove from list
        heldSales.splice(index, 1);
        localStorage.setItem('held_sales_supplier', JSON.stringify(heldSales));
        
        updateHoldBadge();
        $('#holdListModal').modal('hide');
        if(typeof toastr !== 'undefined') { toastr.info('Held Sale Resumed!'); }
    }

    function updateHoldBadge() {
        let heldSales = JSON.parse(localStorage.getItem('held_sales_supplier')) || [];
        $('.hold-count-badge').text(heldSales.length);
        if(heldSales.length > 0) {
            $('.hold-count-badge').removeClass('badge-light').addClass('badge-danger');
        } else {
            $('.hold-count-badge').removeClass('badge-danger').addClass('badge-light');
        }
    }

    $(document).ready(function() {
        updateHoldBadge();
    });
</script>

@endsection