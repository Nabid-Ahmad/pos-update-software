@extends('admin.master')

@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-sm-12">
                <div class="bs-componen">
                    <div class="card">
                        <h5 class="card-header bg-primary text-white">
                            <span class="float-left header-title">
                                <i class="fa fa-plus"></i> Supplier Payment
                            </span>
                            <a href="{{ route('due-collections.index', ['type' => request('type')]) }}"
                               class="btn btn-info btn-sm"
                               style="float: right; "><i class="fa fa-list"></i> Payment / Collection List</a>
                        </h5>

                        <div class="card-body">
                            @include('partials._alert_message')

                            <form action="/due-collections?type={{ request('type') }}" method="POST" id="supplierDueForm">
                                @csrf
                                <input type="hidden" name="action" id="action_type" value="payment">
                                <input type="hidden" name="payment_type" id="payment_type" value="advance">

                                <div class='row'>
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group row">
                                                    <label class="control-label col-3"><b>Supplier Name</b></label>
                                                    <div class="col-md-9">
                                                        <input type="text" id="customer" autofocus
                                                               required
                                                               value="{{ old('supplier_name', optional($selected_supplier ?? null)->name) }}"
                                                               class="form-control" placeholder="Search Supplier..">
                                                        <input type="hidden" name="id" id="supplier_id" value="{{ old('id', optional($selected_supplier ?? null)->id) }}">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="control-label col-md-3">Supplier Code</label>
                                                    <div class="col-md-9">
                                                        <input type="text" readonly name="supplier_code"
                                                               value="{{ old('supplier_code', optional($selected_supplier ?? null)->supplier_code) }}" class="form-control"
                                                               id="supplier_code">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="control-label col-md-3">Mobile Number</label>
                                                    <div class="col-md-9">
                                                        <input type="text" readonly name="phone_number"
                                                               value="{{ old('phone_number', optional($selected_supplier ?? null)->phone) }}" class="form-control"
                                                               id="phone_number">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="control-label col-md-3">Advanced (We Get)</label>
                                                    <div class="col-md-9">
                                                        <input type="text" readonly name="advanced_payment"
                                                               value="{{ old('advanced_payment', isset($selected_supplier) && $selected_supplier->current_balance < 0 ? number_format(abs($selected_supplier->current_balance), 2, '.', '') : '0.00') }}"
                                                               class="form-control text-success" id="advanced_payment"
                                                               style="font-size: 17px; font-weight: bold;">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="control-label col-md-3">Previous Due (Payable)</label>
                                                    <div class="col-md-9">
                                                        <input type="text" readonly name="payable_dueAmount"
                                                               value="{{ old('payable_dueAmount', isset($selected_supplier) && $selected_supplier->current_balance > 0 ? number_format($selected_supplier->current_balance, 2, '.', '') : '0.00') }}"
                                                               class="form-control text-danger" id="payable_dueAmount"
                                                               style="font-size: 17px; font-weight: bold;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-3"><b>Date</b></label>
                                                    <div class="col-md-8">
                                                        <input type="text" name="date" id="dateField" value="{{ date('Y-m-d') }}" required class="form-control dateField" placeholder="Date">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="control-label col-md-3">Select Account</label>
                                                    <div class="col-md-8">
                                                        <select name="account_id" required class="form-control select-account select2">
                                                            <option value="">Select One</option>
                                                            @foreach($accounts as $account)
                                                                 <option value="{{$account->id}}" data-total-amount ="{{ $account->total_amount }}">{{ $account->account_name }} (Balance: {{ number_format($account->total_amount, 2) }})</option>
                                                            @endforeach
                                                        </select>
                                                        <p class="account-balance py-0 my-0 text-info"></p>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="control-label col-md-3">Payment Method</label>
                                                    <div class="col-md-8">
                                                        <select name="payment_method_id" class="form-control select2">
                                                            @foreach($paymentMethods as $id => $name)
                                                                <option value="{{ $id }}" {{ old('payment_method_id') == $id ? 'selected' : '' }}>{{ ucwords($name) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Discount -->
                                                <div class="form-group row" id="discount-row">
                                                    <label class="control-label col-md-3">Discount</label>
                                                    <div class="col-md-8">
                                                        <input type="text" onkeypress="return event.charCode == 46 || event.charCode >= 48 && event.charCode <= 57" name="discount" class="form-control discount" autocomplete="off" style="font-size: 17px">
                                                    </div>
                                                </div>

                                                <div class="form-group row ">
                                                    <label class="control-label col-md-3" id="amount-label">
                                                        <b>Payment Amount</b>
                                                    </label>
                                                    <div class="col-md-8" >
                                                        <input type="text" required name="amount" class="form-control given-amount" onkeypress="return event.charCode == 46 || event.charCode >= 48 && event.charCode <= 57" style="font-size: 17px" placeholder="Enter amount..">
                                                    </div>
                                                </div>

                                                <div class="form-group row ">
                                                    <label class="control-label col-md-3">Balance (After)</label>
                                                    <div class="col-md-8" >
                                                        <input type="text" class="form-control current-amount" name="current_due" readonly style="font-size: 17px; font-weight: bold;">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="control-label col-md-3">Note</label>
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="note" placeholder="Transaction note..">
                                                    </div>
                                                </div>
                                                <br>
                                                <hr>
                                                <div class="form-group row">
                                                    <div class="col-md-8 offset-md-3">
                                                        <button class="btn btn-primary pull-right" type="submit" id="submit-btn">
                                                            <i class="fa fa-fw fa-lg fa-check-circle"></i>
                                                            <span id="submit-text">Pay to Supplier</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
@section('footer-script')
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script type="text/javascript">
        jQuery(function ($) {

            $('.select-account').change(function () {
                if ($('.select-account').val() == '') {
                    $('.account-balance').text('');
                } else {
                    $('.account-balance').text($('.select-account option:selected').data('total-amount'));
                }
            });

            // Prevent given amount greater than account balance
            $('.given-amount, .discount').on('input keyup', function () {
                let amount = parseFloat($('.given-amount').val()) || 0;
                let accountBalanceText = $('.account-balance').text();

                if (amount > 0 && accountBalanceText === '') {
                    alert('Please select an account first');
                    $(this).val('');
                    return;
                }

                let accountBalance = parseFloat(accountBalanceText) || 0;
                if (amount > accountBalance) {
                    alert('Payment amount cannot be greater than ' + $('.select-account option:selected').text() + ' account balance (' + accountBalance.toFixed(2) + ')');
                    $(this).val('');
                    return;
                }

                calculateCurrentBalance();
            });

            $("#customer").autocomplete({
                source: "{{ route('get-supplier') }}",
                minLength: 1,
                select: function (key, value) {
                    $('#customer').val(value.item.value);
                    $('#supplier_id').val(value.item.id);
                    $('#supplier_code').val(value.item.supplier_code);
                    $('#phone_number').val(value.item.phone);
                    getSupplierBalance(value.item.id);
                },
                autoFocus: true
            });

            // Auto calculate on load if supplier is pre-selected
            if ($('#supplier_id').val()) {
                calculateCurrentBalance();
            }
        });

        function calculateCurrentBalance() {
            let advance = parseFloat($('#advanced_payment').val()) || 0;
            let previous_due = parseFloat($('#payable_dueAmount').val()) || 0;
            let amount = parseFloat($('.given-amount').val()) || 0;
            let discount = parseFloat($('.discount').val()) || 0;

            let result = previous_due - advance - amount - discount;
            $('.current-amount').val(result.toFixed(2));
        }

        function getSupplierBalance(supplier_id) {
            if (supplier_id != undefined && supplier_id != '') {
                let baseUrl = "/get-supplier-balance/" + supplier_id;
                $.ajax({
                    url: baseUrl,
                    type: 'GET',
                    success: function (res) {
                        let prevDue = parseFloat(res.previous_due) || 0;
                        let advPay = parseFloat(res.advanced_payment) || 0;
                        $('#payable_dueAmount').val(prevDue.toFixed(2));
                        $('#advanced_payment').val(advPay.toFixed(2));
                        calculateCurrentBalance();
                    }
                });
            }
        }
    </script>
@stop
