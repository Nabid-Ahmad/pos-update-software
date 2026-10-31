<!DOCTYPE html>
<html>
<head>
    <title>Invoice PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #333; line-height: 1.4; }
        .header-table { width: 100%; border: none; margin-bottom: 20px; }
        .header-table td { border: none; padding: 0; vertical-align: top; }
        .company-info { text-align: center; }
        .company-info h2 { margin: 0; font-size: 22px; color: #333; }
        .company-info h6 { margin: 5px 0 0 0; font-size: 12px; font-weight: normal; }
        .info-table { width: 100%; border: none; margin-bottom: 20px; }
        .info-table td { border: none; padding: 0; vertical-align: top; }
        .bill-to strong { font-size: 16px; }
        .invoice-details { text-align: right; }
        .invoice-details h2 { color: blue; margin: 0 0 5px 0; font-size: 22px; }
        .invoice-details h5 { margin: 2px 0; font-size: 13px; font-weight: normal; }
        .text-secondary { color: #6c757d; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 5px; text-align: left; }
        .items-table th { background-color: #f8f9fa; text-align: center; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        
        .summary-table { width: 100%; border: none; }
        .summary-table td { border: none; padding: 0; vertical-align: top; }
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { border: none; padding: 3px 5px; text-align: right; }
        .totals-table .border-top td { border-top: 1px solid #dee2e6; font-weight: bold; }
        .text-danger { color: #dc3545; }
        .text-success { color: #28a745; }
    </style>
</head>
<body>
    @php
        $settings = \App\SoftwareSetting::companies()->get();
        $has_additional_item_field = optional($settings->where('title', 'Need Additional Item Id Field')->first())->options == 'need-additional-item-id-field';
        $col_span = $has_additional_item_field ? 6 : 5;
        if ($sale->sale_type == 'Package') {
            $col_span--;
        }

        $actual_discount = $sale->invoice_discount;
        $actual_special_discount = $sale->special_discount;

        $invoice_total_amount = $sale->total_sale_amount + $sale->invoice_tax + ($sale->currier_amount ?? 0) - $actual_discount - $actual_special_discount;
        $total_payable_amount = $invoice_total_amount + ($sale->previous_due ?? 0);
    @endphp

    <div class="company-info">
        @if (file_exists(public_path($sale->sale_company->company_logo)))
            <img height="80" width="80" src="{{ public_path($sale->sale_company->company_logo) }}" style="float: left">
        @endif
        <h2>{{ $company->name }}</h2>
        <h6>
            <span>Phone: {{ $company->phone }}</span><br>
            <span>Email: {{ $company->email }}</span><br>
            @if(!empty($company->website))
                <span>Website: {{ $company->website }}</span><br>
            @endif
            <span>{{ $company->address }}</span>
        </h6>
        <div style="clear: both;"></div>
    </div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

    <table class="info-table">
        <tr>
            <td class="bill-to" style="width: 50%;">
                <strong style="font-size: 16px;">Bill To</strong><br>
                <strong>{{ optional($sale->sale_customer)->name }}</strong><br>
                #{{ optional($sale->sale_customer)->customerId }}<br>
                Address : {{ optional($sale->sale_customer)->address }}<br>
                Phone : {{ optional($sale->sale_customer)->phone }}<br>
                Email: {{ optional($sale->sale_customer)->email }}
            </td>
            <td class="invoice-details" style="width: 50%;">
                <h2>INVOICE</h2>
                <h5><span class="text-secondary">Date :</span> {{ fdate($sale->sale_date, 'Y-m-d') }}</h5>
                <h5><span class="text-secondary">Invoice ID:</span> {{ $sale->invoiceId }}</h5>
                <h5><span class="text-secondary">Sold By:</span> {{ optional($sale->sale_created_by)->name ?? 'admin' }}</h5>
                @if($sale->marketers_id)
                <h5><span class="text-secondary">Marketer Name:</span> {{ optional($sale->marketers)->marketers_name }} - {{ optional($sale->marketers)->marketers_mobile }}</h5>
                @endif
                @if($sale->currier_id)
                <h5><span class="text-secondary">Delivery Man:</span> {{ optional($sale->currier)->name }}</h5>
                @endif
                @if(optional($sale->saleMeta)->delivered_by)
                <h5><span class="text-secondary">Delivered By:</span> {{ optional(optional($sale->saleMeta)->delivered)->name }}</h5>
                @endif
                <h5><span class="text-secondary">Supplier Name:</span> {{ optional($sale->supplier)->name ?? 'N/A' }}</h5>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
        <tr>
            <th width="5%" class="text-center">SL NO.</th>
            @if ($has_additional_item_field)
                <th width="15%">Item Ids</th>
            @endif
            <th class="text-center">PRODUCT DESCRIPTION</th>
            <th width="15%" class="text-center">UNIT PRICE</th>
            <th width="10%" class="text-center">QTY</th>
            <th width="15%" class="text-right">AMOUNT</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($sale->sale_details as $key => $details)
            <tr>
                <td class="text-center">{{ $key + 1 }}</td>
                @if ($has_additional_item_field)
                    <td>{{ $details->product_item_ids }}</td>
                @endif
                <td>
                    {{ ucwords(optional($details->product)->product_name) }}
                    @if($details->product_discount > 0)
                        <small class="text-danger">(Dis: {{ floatval($details->product_discount) }}%)</small>
                    @endif
                    @if($details->serials->count() > 0)
                        <br><small>Serials: {{ implode(', ', $details->serials->pluck('serial')->toArray()) }}</small>
                    @endif
                </td>
                <td class="text-center">{{ number_format($details->unit_price, 2) }}</td>
                <td class="text-center">{{ $details->quantity }} {{ optional(optional($details->product)->product_unit)->name }}</td>
                <td class="text-right">{{ number_format($details->product_sub_total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td style="width: 60%; padding-right: 20px;">
                <p style="font-weight: bold; margin-top: 0;">In Words: {{ ucwords(str_replace('-', ' ', inWords($total_payable_amount))) }}</p>
                @if($sale->note)
                    <p style="margin-top: 15px; margin-bottom: 0;"><strong>Note:</strong> {{ $sale->note }}</p>
                @endif
                <div style="margin-top: 50px;">
                    @if(!empty($sale->sale_company->invoice_footer))
                        {!! nl2br(e($sale->sale_company->invoice_footer)) !!}
                    @else
                        <span style="font-weight: bold; text-decoration: underline;">Warrenty Terms and Conditions:</span><br>
                        <small>Warranty Void if Warranty Sticker removed and any physical damage and burned case.</small>
                    @endif
                </div>
            </td>
            <td style="width: 40%;">
                <table class="totals-table">
                    <tr>
                        <td>Sub Total :</td>
                        <td width="40%">{{ number_format($sale->total_sale_amount, 2) }}</td>
                    </tr>
                    @php
                        $calc_disc_pct = ($sale->discount_type == '%' && $sale->total_sale_amount > 0) ? ($sale->invoice_discount / $sale->total_sale_amount) * 100 : 0;
                        $remaining = $sale->total_sale_amount - $sale->invoice_discount;
                        $calc_spec_pct = ($sale->special_discount_type == '%' && $remaining > 0) ? ($sale->special_discount / $remaining) * 100 : 0;
                    @endphp
                    <tr>
                        <td>Discount ({{ $sale->discount_type == '%' ? rtrim(rtrim(number_format($calc_disc_pct, 2), '0'), '.') . '%' : 'flat' }}) :</td>
                        <td>{{ number_format($actual_discount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Special Discount ({{ $sale->special_discount_type == '%' ? rtrim(rtrim(number_format($calc_spec_pct, 2), '0'), '.') . '%' : 'flat' }}) :</td>
                        <td>{{ number_format($actual_special_discount, 2) }}</td>
                    </tr>
                    @if($sale->invoice_tax > 0)
                    <tr>
                        <td>Tax :</td>
                        <td>{{ number_format($sale->invoice_tax, 2) }}</td>
                    </tr>
                    @endif
                    @if($sale->currier_amount > 0)
                    <tr>
                        <td>Courier Charge :</td>
                        <td>{{ number_format($sale->currier_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="border-top">
                        <td>Invoice Total :</td>
                        <td>{{ number_format($invoice_total_amount, 2) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td>Total Payable :</td>
                        <td>{{ number_format($total_payable_amount, 2) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td>Paid Amount :</td>
                        <td>{{ number_format($sale->receive_amount, 2) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td>Due Amount :</td>
                        <td>{{ $sale->balance > 0 ? number_format($sale->balance, 2) : '0.00' }}</td>
                    </tr>
                    @if($sale->change_amount > 0)
                    <tr class="border-top">
                        <td>Change Return :</td>
                        <td class="text-success">{{ number_format($sale->change_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Customer Current Balance :</td>
                        <td>{{ $sale->balance > 0 ? number_format($sale->balance, 2) : '0.00' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
