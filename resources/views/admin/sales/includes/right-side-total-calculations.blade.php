<div class="col-md-3">
    <div class="card card-body p-2" style="font-size: 13px;">
        <div class="input-group input-group-sm mb-1">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text" style="width: 110px;">Invoice Total</span>
            </div>
            <input type="text" name="invoice_total" class="form-control invoice-total bg-light" value="0" readonly>
        </div>

        <div class="input-group input-group-sm mb-1">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text" style="width: 110px;">Discount</span>
            </div>
            <input type="text" name="discount_percent" class="form-control discount-percent" placeholder="%" value="0">
            <input type="text" name="invoice_discount" class="form-control discount-amount" placeholder="Flat" value="0">
        </div>

        <div class="input-group input-group-sm mb-1">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text" style="width: 110px;">Special Disc</span>
            </div>
            <input type="text" name="special_discount_percent" class="form-control special-discount-percent" placeholder="%" value="0">
            <input type="text" name="special_discount_amount" class="form-control special-discount-amount" placeholder="Flat" value="0">
        </div>

        <div class="input-group input-group-sm mb-1">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text" style="width: 110px;" title="Payable Amounts" data-toggle="tooltip">P. Amounts</span>
            </div>
            <input type="text" name="sub_total" class="form-control payable-amount bg-light" value="0" readonly>
        </div>

        <div class="input-group input-group-sm mb-1">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text" style="width: 110px;">Delivery Charge</span>
            </div>
            <input type="text" name="delivery_charge" class="form-control delivery-charge" value="0">
        </div>

        <div class="input-group input-group-sm mb-1">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text" style="width: 110px;">Delivery Payer</span>
            </div>
            <select name="delivery_charge_payer" class="form-control delivery-payer bg-light font-weight-bold">
                <option value="company" {{ $default_delivery_payer == 'company' ? 'selected' : '' }}>Company Pay</option>
                <option value="customer" {{ $default_delivery_payer == 'customer' ? 'selected' : '' }}>Customer Pay</option>
            </select>
        </div>

        <div class="input-group input-group-sm mb-1">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text" style="width: 110px;">Previous Due</span>
            </div>
            <input type="text" name="previous_due" class="form-control previous-due text-danger bg-light" value="0" readonly>
        </div>

        <div class="input-group input-group-sm mb-1">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text" style="width: 110px;">Advanced</span>
            </div>
            <input type="text" name="advanced_payment" class="form-control advanced-payment bg-light" value="0" readonly>
        </div>

        <div class="input-group input-group-sm mb-1">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text" style="width: 110px;">Total Payable</span>
            </div>
            <input type="text" name="total_payable" class="form-control total-payable-amount bg-light" value="0" readonly>
        </div>

        <div class="input-group input-group-sm mb-1">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text" style="width: 110px;">Current Balance</span>
            </div>
            <input type="text" name="current_balance" class="form-control current-balance bg-light" value="0" readonly>
        </div>

        <div class="input-group input-group-sm mb-1">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text" style="width: 110px;">Delivered By</span>
            </div>
            <input type="text" name="deliverer" class="form-control deliverer" placeholder="Order Delivered By">
        </div>

        <div class="form-group mb-1">
            <select name="fk_account_id" class="form-control select-account select2 account-info" required style="height: 30px;" title="Select Transaction Account" data-toggle="tooltip">
                <option value="">Select Account</option>
                @foreach($account_infos as $account)
                    <option value="{{ $account->id }}" data-total-amount="{{ $account->total_amount }}">{{ $account->account_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="input-group input-group-sm mb-1">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text font-weight-bold bg-warning" style="width: 110px; color: black;">Receive [F8]</span>
            </div>
            <!-- Anti-autofill dummy field -->
            <input type="text" name="email" style="display:none;" value="fake@email.com">
            <input type="text" name="receive_amount" class="form-control receive-amount font-weight-bold" value="0" autocomplete="new-password" style="font-size: 16px;">
        </div>

        <div class="input-group input-group-sm mb-2">
            <div class="input-group-prepend" style="height: 30px;">
                <span class="input-group-text font-weight-bold bg-success text-white" style="width: 110px;">Change Return</span>
            </div>
            <input type="text" name="change_amount" id="change_amount" class="form-control change-return bg-light font-weight-bold text-success" value="0" readonly style="font-size: 16px;">
        </div>

        <div class="form-group mb-1 p-1 bg-light border d-flex align-items-center" style="height: 30px;">
            <i class="fa fa-eye-slash" id="profit-eye-icon" onclick="toggleProfitVisibility()" style="cursor: pointer;"></i>
            <span class="ml-2 text-success" style="font-size: 20px; line-height: 1;">●</span>
            <span id="profit-display" class="ml-2 font-weight-bold" style="display:none;">0.00</span>
            <input type="hidden" id="total-profit-amount" value="0.00">
        </div>

        <div class="form-group text-center mb-1">
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="print_pos" name="print_type" class="custom-control-input" value="pos" {{ $is_default_pos_print ? 'checked' : '' }}>
                <label class="custom-control-label" for="print_pos" style="font-size: 12px;">POS Print</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="print_normal" name="print_type" class="custom-control-input" value="normal" {{ !$is_default_pos_print ? 'checked' : '' }}>
                <label class="custom-control-label" for="print_normal" style="font-size: 12px;">Normal Print</label>
            </div>
        </div>

        @php
            $showMailInvoice = \App\SoftwareSetting::where('title', 'Send Invoice Mail To Customer')->where('options', 'yes')->where('fk_company_id', auth()->user()->fk_company_id)->count() > 0;
            $showSmsInvoice = \App\SoftwareSetting::where('title', 'Send Invoice Sms To Customer')->where('options', 'yes')->where('fk_company_id', auth()->user()->fk_company_id)->count() > 0;
        @endphp

        @if ($showMailInvoice || $showSmsInvoice)
        <div class="form-group text-center mb-1">
            @if($showSmsInvoice)
            <div class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" id="is_send_message" name="is_send_message" class="custom-control-input" value="1">
                <label class="custom-control-label text-success font-weight-bold" for="is_send_message" style="font-size: 13px;"><i class="fa fa-commenting"></i> Send SMS</label>
            </div>
            @endif
            @if($showMailInvoice)
            <div class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" id="send_email_invoice" name="send_email_invoice" class="custom-control-input" value="1">
                <label class="custom-control-label text-primary font-weight-bold" for="send_email_invoice" style="font-size: 13px;"><i class="fa fa-envelope"></i> Email Invoice</label>
            </div>
            @endif
        </div>
        @endif

        <div class="row no-gutters mt-2">
            <div class="col-6 pr-1">
                <button type="button" class="btn btn-primary btn-sm btn-block save-btn" onclick="saveSaleInvoice()">
                    <i class="fa fa-save"></i> Save (F10)
                </button>
            </div>
            <div class="col-6 pl-1">
                <a href="{{ route('sales.index') }}" class="btn btn-success btn-sm btn-block">
                    List
                </a>
            </div>
        </div>

        @if (\App\SoftwareSetting::where('title', 'Hold Option In Sale')->where('options', 'yes')->where('fk_company_id', auth()->user()->fk_company_id)->count() > 0)
        <div class="row no-gutters mt-2">
            <div class="col-12">
                <button type="button" class="btn btn-warning btn-sm btn-block hold-btn" onclick="holdCurrentSale()">
                    <i class="fa fa-hand-paper-o"></i> Hold Sale
                </button>
            </div>
        </div>
        @endif

        <!-- Recent Invoices Sidebar View -->
        {{--
        <div class="card mt-2 border-info">
            <div class="card-header bg-info text-white p-1 text-center font-weight-bold" style="font-size: 12px;">
                <i class="fa fa-history"></i> Recent Invoices
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" style="font-size: 11px;">
                    @forelse($recent_sales ?? [] as $recent)
                        <li class="list-group-item p-1 d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $recent->invoiceId ?? 'INV-'.$recent->id }}</strong><br>
                                <span class="text-muted">{{ optional($recent->sale_customer)->name ?? 'Unknown' }}</span>
                            </div>
                            <div class="text-right">
                                <strong>৳ {{ number_format($recent->payable ?? 0, 2) }}</strong><br>
                                <a href="{{ route('sales.show-v2', $recent->id) }}?print_type=pos-invoice" target="_blank" class="text-info" title="Print POS"><i class="fa fa-print"></i></a>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item p-1 text-center text-muted">No recent sales</li>
                    @endforelse
                </ul>
            </div>
        </div>
        --}}
    </div>
</div>
