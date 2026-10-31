@php
    $has_additional_item_field = optional($settings->where('title', 'Need Additional Item Id Field')->first())->options == 'need-additional-item-id-field';
    $invoiceBarcode = optional($settings->where('title', 'Show Barcode in Invoice')->first())->options == 'yes';
    $col_span = $has_additional_item_field ? 7 : 6;
    if ($sale->sale_type == 'Package') {
        $col_span--;
    }
@endphp

@extends('admin.master')
@section('title', ' - Invoice Report')

@push('style')
    <style type="text/css">
        @media print {
            .d-none { display: block !important; }
            .d-print { display: block !important; }
        }
        @page { margin: 1in; }
        .d-print { display: none; }
        .html5-endscreen { top: 60px; display: none !important; }
    </style>
@endpush

@section('content')





    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-file-text-o"></i> Invoice</h1>
                <p>Sale Invoice</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="#">Invoice</a></li>
            </ul>
        </div>

        <div class="row mx-auto" style="width: 100%">
            <div class="col-md-12">
                <div class="tile" style="border: none !important;">
                    <section class="invoice tile-body" style="min-height: 740px !important;">
                        <div class="row mb-4">
                            <div class="col-12 text-center">
                                <img height="100" width="100" src="{{ asset($sale->sale_company->company_logo) }}" style="float: left">
                                <h2 class="page-header" style="font-size: 38px !important; font-weight: bold; color: black !important; margin-bottom: 5px;">{{ $sale->sale_company->name }}</h2>
                                <p style="font-size: 22px !important;text-align: center">
                                    <span>Phone: {{ $sale->sale_company->phone }}</span><br>
                                    <span>Email: {{ $sale->sale_company->email }}</span><br>
                                    @if(!empty($sale->sale_company->website))
                                        <span>Website: {{ $sale->sale_company->website }}</span><br>
                                    @endif
                                    <span>{{ $sale->sale_company->address }}</span><br>
                                </p>
                            </div>
                        </div>

                        <div class="row invoice-info">
                            <div class="col-6">
                                <strong style="font-size: 20px">Bill To</strong>
                                <address style="font-size: 20px !important;">
                                    <strong>{{ $sale->sale_customer->name }}</strong><br>
                                    <strong>{{ $sale->sale_customer->customerId }}</strong><br>
                                    Address : {{ $sale->sale_customer->address }}<br>
                                    Phone : {{ $sale->sale_customer->phone }}<br>
                                    Email: {{ $sale->sale_customer->email }}
                                    @if ($sale->currier_id != '')
                                        <br>Courier: {{ $sale->currier->name }}
                                    @endif
                                </address>
                            </div>

                            <div class="col-6">
                                <h2 class="text-right" style="color: blue">INVOICE</h2>
                                <h4 class="text-right">
                                    <span class="text-secondary">Date :</span>
                                    {{ fdate($sale->sale_date, 'Y-m-d') }}
                                </h4>
                                <h4 class="text-right">
                                    <span class="text-secondary">Invoice ID:</span>
                                    {{ $sale->invoiceId }}
                                </h4>
                                <h4 class="text-right">
                                    <span class="text-secondary">Sold By:</span>
                                    {{ @$sale->created_user->name }}
                                </h4>
                                @if($sale->marketers_id)
                                <h4 class="text-right">
                                    <span class="text-secondary">Marketer Name:</span>
                                    {{ optional($sale->marketers)->marketers_name }} - {{ optional($sale->marketers)->marketers_mobile }}
                                </h4>
                                @endif
                                @if($sale->currier_id)
                                <h4 class="text-right">
                                    <span class="text-secondary">Delivery Man:</span>
                                    {{ optional($sale->currier)->name }}
                                </h4>
                                @endif
                                @if(optional($sale->saleMeta)->delivered_by)
                                <h4 class="text-right">
                                    <span class="text-secondary">Delivered By:</span>
                                    {{ optional(optional($sale->saleMeta)->delivered)->name }}
                                </h4>
                                @endif
                                @if($sale->supplier_id)
                                <h4 class="text-right">
                                    <span class="text-secondary">Supplier Name:</span>
                                    {{ optional($sale->sale_supplier)->name }}
                                </h4>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 table-responsive">
                                <table class="table table-sm b" style="border: none">
                                    <thead>
                                        <tr style="font-size: 16px;">
                                            <th class="text-center" style="border: 1px solid black;width: 5%">SL NO.</th>
                                            <th class="text-center" style="border: 1px solid black; width: 55%;">PRODUCT DESCRIPTION</th>
                                            <th class="text-center" style="border: 1px solid black; width: 10%">UNIT PRICE</th>
                                            <th class="text-center" style="border: 1px solid black; width: 10%">QTY</th>
                                            <th class="text-center" style="border: 1px solid black; width: 10%">AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody style="border: none;">
                                        @foreach ($sale->sale_details as $key => $details)
                                            <tr>
                                                <td class="text-center" style="border: 1px solid black; font-size: 18px !important;">{{ $key + 1 }}</td>
                                                @if ($has_additional_item_field)
                                                    <td style="border: 1px solid black; font-size: 18px !important;">{{ $details->product_item_ids }}</td>
                                                @endif
                                                <td style="border: 1px solid black; font-size: 18px !important;">
                                                    {{ ucwords(optional($details->product)->product_name) }}
                                                    @if($details->product_discount > 0)
                                                        <small class="text-danger">(Dis: {{ floatval($details->product_discount) }}%)</small>
                                                    @endif
                                                    <br>
                                                    {{ ucwords(optional($details->product)->product_description) }}<br>
                                                    <p style="margin: 0">
                                                        @foreach ($details->serials ?? [] as $item)
                                                            <span class="text-dark pr-1 rounded">{{ $item->serial }}</span>
                                                        @endforeach
                                                    </p>
                                                </td>
                                                <td style="border: 1px solid black; font-size: 18px !important;" class="text-center">{{ $details->unit_price }}</td>
                                                <td class="text-center" style="font-size: 14px !important; border: 1px solid black;">
                                                    {{ (float) $details->quantity . ' ' . optional(optional($details->product)->product_unit)->name }}
                                                </td>
                                                <td style="border: 1px solid black; font-size: 18px !important;" class="text-center">{{ $details->product_sub_total }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                @php
                                    $sale->total_sale_amount = $sale->sale_details->sum('product_sub_total');
                                @endphp
                                <table width="100%">
                                    <tr>
                                        <td colspan="{{ $col_span }}" style="border: none; padding: 0 !important; font-size: 18px !important; font-weight: bold">
                                            <div class="col-8">
                                                <p class="font-weight-bold">In Words: {{ ucwords(str_replace('-', ' ', inWords($sale->total_payable))) }}</p>
                                                @if($sale->note)
                                                    <p style="margin-top: 15px; margin-bottom: 0;"><strong>Special Note:</strong> {{ $sale->note }}</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important;">Sub Total :</td>
                                        <td class="text-right" style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important;">
                                            {{ number_format($sale->total_sale_amount, 2) }}
                                        </td>
                                    </tr>

                                    @php
                                        $total_discount_amount = $sale->invoice_discount + $sale->special_discount;
                                        $discount_text_parts = [];
                                        
                                        if ($sale->invoice_discount > 0) {
                                            if ($sale->discount_type == '%') {
                                                $calc_pct = $sale->total_sale_amount > 0 ? ($sale->invoice_discount / $sale->total_sale_amount) * 100 : 0;
                                                $discount_text_parts[] = rtrim(rtrim(number_format($calc_pct, 2), '0'), '.') . '%';
                                            } else {
                                                $discount_text_parts[] = number_format($sale->invoice_discount, 2);
                                            }
                                        }
                                        if ($sale->special_discount > 0) {
                                            if ($sale->special_discount_type == '%') {
                                                $remaining = $sale->total_sale_amount - $sale->invoice_discount;
                                                $calc_pct = $remaining > 0 ? ($sale->special_discount / $remaining) * 100 : 0;
                                                $discount_text_parts[] = rtrim(rtrim(number_format($calc_pct, 2), '0'), '.') . '%';
                                            } else {
                                                $discount_text_parts[] = number_format($sale->special_discount, 2);
                                            }
                                        }
                                        
                                        $discount_display = implode(' + ', $discount_text_parts);
                                        if (empty($discount_display) && $total_discount_amount > 0) {
                                            $discount_display = number_format($total_discount_amount, 2);
                                        }
                                    @endphp

                                    @if($total_discount_amount > 0)
                                    <tr style="border: none">
                                        <td colspan="{{ $col_span }}" style="border: none; padding: 0 !important;"></td>
                                        <td style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important;">
                                            Discount :
                                        </td>
                                        <td class="text-right" style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important;">
                                            {{ $discount_display }}
                                        </td>
                                    </tr>
                                    @endif

                                    @if ($sale->currier_amount != '')
                                    <tr style="border: none;">
                                        <td colspan="{{ $col_span }}" style="border: none; padding: 0 !important;"></td>
                                        <td style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important;">Courier Charge :</td>
                                        <td class="text-right" style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important;">
                                            {{ number_format($sale->currier_amount, 2) }}
                                        </td>
                                    </tr>
                                    @endif

                                    @php
                                        $courier = $sale->delivery_charge_payer == 'customer' ? floatval($sale->currier_amount ?? $sale->delivery_charge ?? 0) : 0;
                                        $current_invoice_total = $sale->total_sale_amount - $sale->invoice_discount - $sale->special_discount + $sale->invoice_tax + $sale->cod_amount + $courier;
                                        $total_payable = $current_invoice_total + floatval($sale->previous_due ?? 0) - floatval($sale->advanced ?? 0);
                                        $live_due = $total_payable - $sale->paid;
                                    @endphp
                                    <tr style="border: none;">
                                        <td colspan="{{ $col_span }}" style="border: none; padding: 0 !important;"></td>
                                        <td style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important; font-weight: bold;">Invoice Total :</td>
                                        <td class="text-right" style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important; font-weight: bold;">
                                            {{ number_format($current_invoice_total, 2) }}
                                        </td>
                                    </tr>

                                    @if(isset($sale->previous_due))
                                    <tr style="border: none;">
                                        <td colspan="{{ $col_span }}" style="border: none; padding: 0 !important;"></td>
                                        <td style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important;">Previous Due :</td>
                                        <td class="text-right" style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important;">
                                            {{ number_format($sale->previous_due, 2) }}
                                        </td>
                                    </tr>
                                    @endif

                                    <tr style="border: none;">
                                        <td colspan="{{ $col_span }}" style="border: none; padding: 0 !important;"></td>
                                        <td style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important;">Advanced :</td>
                                        <td class="text-right" style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important;">
                                            {{ number_format($sale->advanced, 2) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="{{ $col_span }}" style="border: none;"></td>
                                        <td style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important; font-weight: bold; border-top: 1px solid #000;">Total Payable :</td>
                                        <td class="text-right" style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important; font-weight: bold; border-top: 1px solid #000;">
                                            {{ number_format($total_payable, 2) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="{{ $col_span }}" style="border: none;"></td>
                                        <td style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important;">Paid Amount :</td>
                                        <td class="text-right" style="text-align:right; border: none; padding: 0 !important; font-size: 16px !important;">
                                            {{ number_format($sale->paid, 2) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="{{ $col_span }}" style="border: none; padding: 0 !important;"></td>
                                        <td style="text-align:right; border: none; padding: 0 !important; font-size: 18px !important; font-weight: bold;">Due Amount :</td>
                                        <td class="text-right" style="text-align:right; border: none; padding: 0 !important; font-size: 18px !important; font-weight: bold;">
                                            {{ $live_due > 0 ? number_format($live_due, 2) : '0.00' }}
                                        </td>
                                    </tr>
                                    @if($sale->change_amount > 0)
                                    <tr>
                                        <td colspan="{{ $col_span }}" style="border: none; padding: 0 !important;"></td>
                                        <td style="text-align:right; border: none; padding: 0 !important; font-size: 18px !important; font-weight: bold;">Change Return :</td>
                                        <td class="text-right" style="text-align:right; border: none; padding: 0 !important; font-size: 18px !important; font-weight: bold;">
                                            {{ number_format($sale->change_amount, 2) }}
                                        </td>
                                    </tr>
                                    @endif

                                    <tr>
                                        <td colspan="{{ $col_span }}" style="border: none; padding: 0 !important;"></td>
                                        <td style="text-align:right; border: none; padding: 0 !important; font-size: 18px !important;">Customer Current Balance :</td>
                                        <td class="text-right" style="text-align:right; border: none; padding: 0 !important; font-size: 18px !important;">
                                            {{ $live_due > 0 ? number_format($live_due, 2) : '0.00' }}
                                        </td>
                                    </tr>
                                </table>

                                @if(!empty($sale->sale_company->invoice_footer))
                                    {!! nl2br(e($sale->sale_company->invoice_footer)) !!}
                                @else
                                    <span style="font-size: 18px; font-weight: bold">Warrenty Terms and Conditions:</span><br>
                                    Warranty Void if Warranty Sticker removed and<br>
                                    any physical damage and burned case.
                                @endif
                            </div>
                        </div>

                        <div class="row d-print-none mt-2">
                            <div class="col-12 text-right">
                                <div class="btn-group btn-corner">
                                    <form action="{{ route('email.send',$sale->id) }}" method="post" style="display: inline-block">
                                        @csrf
                                        <input type="hidden" name="type" value="sale">
                                        <button class="btn btn-sm btn-info" type="submit"><i class="fa fa-envelope"></i> Email</button>
                                    </form>
                                    <a class="btn btn-sm btn-success" href="" onclick="window.print()"><i class="fa fa-print"></i> Print</a>
                                    <a class="btn btn-sm btn-danger text-light" href="{{ route('sales.index') }}"><i class="fa fa-backward"></i> Back To List</a>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <div class="d-print">
            <div class="row">
                <div class="col-sm-12">
                    @if ($invoiceBarcode)
                        <div class="col-sm-12 text-center"></div>
                    @endif
                    <h2 style="font-size: 14px; margin-top: 20px; width: 100%" class="text-center">
                        {{ config('app.thanks_message_in_sale_invoice') ?? 'Thanks For Coming' }}
                    </h2>
                    <div style="width: 40% !important; margin-left: 30px; float: left">
                        <b> Customer : {{ optional($sale->sale_customer)->name }} </b><br><br><br><br><br>
                        <hr>
                        <span style="margin-right: 50px; margin-left: 15px; font-size: 16px;">Signature and Date</span>
                    </div>
                    <div style="width: 40% !important; float: right; margin-left: auto; text-align: right">
                        <b style="margin-right: 50px"> Issued By : {{ optional($sale->sale_company)->name }} </b><br>
                        <br><br><br><br>
                        <hr>
                        <span style="margin-right: 50px; font-size: 16px;">Signature and Date</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 text-center">
                    <p>Note: Goods Sold And Once Received Or Accepted By The Customers Are Not Returnable .</p>
                </div>
            </div>
        </div>
    </main>
    @if(str_contains(\Request::getRequestUri(), 'invoice'))
        <script>
            setTimeout(function() { window.print(); }, 3000);
        </script>
    @endif
@endsection

@section('js')
    @if(session('send_invoice_email_sale_id'))
        <script>
            $(document).ready(function() {
                // Background AJAX send email
                $.post('{{ route("sales.send-invoice-email") }}', {
                    _token: '{{ csrf_token() }}',
                    sale_id: '{{ session("send_invoice_email_sale_id") }}'
                }).done(function(response) {
                    if (response.success) {
                        console.log('Invoice email sent successfully to customer.');
                    } else {
                        console.error('Invoice email failed: ' + response.message);
                    }
                }).fail(function() {
                    console.error('Invoice email failed due to network error.');
                });
            });
        </script>
    @endif
@stop