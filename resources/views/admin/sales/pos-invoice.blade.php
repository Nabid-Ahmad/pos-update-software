

@php
    $has_additional_item_field  = optional($settings->where('title', 'Need Additional Item Id Field')->first())->options == 'need-additional-item-id-field';
    $invoiceBarcode             = optional($settings->where('title', 'Show Barcode in Invoice')->first())->options == 'yes';
    $col_span                   = $has_additional_item_field ? 4 : 3;
@endphp


<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset="UTF-8">
        <title>Invoice - 0000{{ $sale->id }}</title>

        <style>
            @media print {
                .page-break {
                    display: block;
                    page-break-before: always;
                }
            }

            #invoice-POS {
                padding: 0;
                margin: 0 auto;
                width: 71mm;
                background: #FFF;
                font-family: monospace;
            }

            #invoice-POS ::selection {
                background: #f31544;
                color: #FFF;
            }

            #invoice-POS ::moz-selection {
                background: #f31544;
                color: #FFF;
            }

            #invoice-POS h1 {
                font-size: 1.2em;
                color: #222;
            }

            #invoice-POS h2 {
                font-size: 13px;
            }

            #invoice-POS h3 {
                font-size: 1.2em;
                font-weight: 300;
                line-height: 2em;
            }

            #invoice-POS p {
                font-size: 13px;
                color: #666;
                line-height: 1.5em;
            }

            #invoice-POS #top,
            #invoice-POS #mid,
            #invoice-POS #bot {
                /* Targets all id with 'col-' */
                /*border-bottom: 1px solid #EEE;*/
            }

            #invoice-POS #top {
                min-height: 100px;
            }

            #invoice-POS #mid {
                min-height: 80px;
            }

            #invoice-POS #bot {
                min-height: 50px;
            }

            #invoice-POS #top .logo {
                height: 60px;
                width: 60px;
                background-size: 60px 60px;
            }

            #invoice-POS .clientlogo {
                float: left;
                height: 60px;
                width: 60px;
                background-size: 60px 60px;
                border-radius: 50px;
            }

            #invoice-POS .info {
                display: block;
                margin-left: 0;
            }

            #invoice-POS .title {
                float: right;
            }

            #invoice-POS .title p {
                text-align: right;
            }

            #invoice-POS table {
                width: 100%;
                border-collapse: collapse;
            }

            #invoice-POS .tabletitle {
                font-size: .5em;
                background: #EEE;
            }

            #invoice-POS .service {
                border-bottom: 1px solid #EEE;
            }

            #invoice-POS .item {
                width: 24mm;
                padding-left: 2px;
            }

            #invoice-POS .itemtext {
                font-size: 13px;
                /*font-size: .5em;*/
            }

            #invoice-POS #legalcopy {
                margin-top: 5mm;
            }

            @media print {
                .no-print, .no-print * {
                    display: none !important;
                }
            }

            h2 {
                margin: 3px;
            }

            p {
                margin: 3px;
            }
            body {
                background: #fafafa !important;
            }
        </style>

        <script>
            window.console = window.console || function (t) {
            };
        </script>


        <script>
            if (document.location.search.match(/type=embed/gi)) {
                window.parent.postMessage("resize", "*");
            }
        </script>


    </head>

    <body translate="no">

        <div class="no-print" style="text-align: center; margin-bottom: 40px;">
            <button type="button" onclick="window.print()" style=" background-color: #4CAF50; border: none;color: white; padding: 11px 32px;text-align: center;text-decoration: none;display: inline-block;font-size: 16px;cursor: pointer; border-radius: 5px;"> Print </button>
            <a href="{{ url()->previous() }}" style=" background-color: red; border: none;color: white; padding: 11px 32px;text-align: center;text-decoration: none;display: inline-block;font-size: 16px;cursor: pointer; border-radius: 5px;">Back</a>
        </div>


        <div id="invoice-POS">

            <center id="top">
                <div class="info">
                    <h1 style="margin-top: 0;">{{ $sale->sale_company->name }}</h1>
                    <p style="margin-top: -10px; color: black !important;">
                        {{ $sale->sale_company->address }} <br>
                        {{ $sale->sale_company->email }} </br>
                        {{ $sale->sale_company->phone }} </br>
                    </p>
                    <p style="float: left; font-size: 12px; color: black !important;">{{ $sale->sale_customer->name }}</p>
                    <p style="float: right; font-size: 12px; color: black !important;">{{ $sale->sale_customer->phone}}</p>

                    <p style="float: left; font-size: 12px; color: black !important;">Bill No: {{ $sale->invoiceId }}</p>
                    <p style="float: right; font-size: 12px; color: black !important;">Date: {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }} </p>
                    @if ($sale->currier_id != '')
                        <p style="float: left; font-size: 12px; color: black !important;">Courier: {{ $sale->currier->name }}</p>
                    @endif
                </div>
                <!--End Info-->
            </center>
            <!--End InvoiceTop-->
            <!--End Invoice Mid-->

            <div id="bot">

                <div id="table">
                    <table>
                        <tr class="tabletitle">
                            <td class="item">
                                <h2>Item</h2>
                            </td>
                            <td class="Rate">
                                <h2>Rate</h2>
                            </td>
                            <td class="Hours" style="padding-left: 5px;">
                                <h2>Qty</h2>
                            </td>
                            <td class="Rate text-right">
                                <h2 class="text-right">Amount</h2>
                            </td>
                        </tr>

                        @foreach ($sale->sale_details as $key => $details)
                            <tr class="service">
                                <td class="tableitem">
                                    <p class="itemtext" style="font-size: 12px !important; color: black !important;">{{ $details->product->product_name }}
                                        @if($details->product_discount > 0)
                                            <span style="font-size: 10px !important; color: red;">(Dis: {{ floatval($details->product_discount) }}%)</span>
                                        @endif
                                        @if($has_additional_item_field && $details->product_item_ids != null)
                                        <span style="font-size: 10px !important;">({{ $details->product_item_ids }})</span>
                                        @endif
                                    </p>
                                </td>
                                <td class="tableitem">
                                    <p class="itemtext" style=" color: black !important;">{{ $details->unit_price }}</p>
                                </td>
                                <td class="tableitem" style="padding-left: 5px; color: black !important;">
                                    <p class="itemtext" style=" color: black !important;">{{ (float) $details->quantity }}</p>
                                </td>
                                <td class="tableitem">
                                    <p class="itemtext" style=" color: black !important;">{{ $details->product_sub_total }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </table>

                    <hr>
                    @php
                        $sale->total_sale_amount = $sale->sale_details->sum('product_sub_total');
                    @endphp
                    <table style="width: 100%;">
    <tr class="tabletitle" style="margin: 0px;">
        <td style="width: 35%"></td>
        <td class="Rate" style="text-align: right; padding-right: 10px;">
            <h2 style="font-size: 14px; margin: 5px 0;">Sub Total</h2>
        </td>
        <td class="payment" style="text-align: right;">
            <h2 style="font-size: 14px; margin: 5px 0;"> {{ number_format($sale->total_sale_amount , 2)}}</h2>
        </td>
    </tr>

    <tr class="tabletitle">
        <td style="width: 35%"></td>
        <td class="Rate" style="text-align: right; padding-right: 10px;">
            <h2 style="font-size: 14px; margin: 5px 0;">Discount</h2>
        </td>
        <td class="payment" style="text-align: right;">
            <h2 style="font-size: 14px; margin: 5px 0;"> {{ number_format($sale->invoice_discount, 2) }}</h2>
        </td>
    </tr>

    <tr class="tabletitle">
        <td style="width: 35%"></td>
        <td class="Rate" style="text-align: right; padding-right: 10px;">
            <h2 style="font-size: 14px; margin: 5px 0;">Special Discount</h2>
        </td>
        <td class="payment" style="text-align: right;">
            <h2 style="font-size: 14px; margin: 5px 0;"> {{ number_format($sale->special_discount, 2) }}</h2>
        </td>
    </tr>

    @if ($sale->currier_amount != '')
        <tr class="tabletitle">
            <td colspan="2" class="Rate" style="text-align: right; padding-right: 10px;">
                <h2 style="font-size: 14px; margin: 5px 0;">Courier Charge</h2>
            </td>
            <td class="payment" style="text-align: right;">
                <h2 style="font-size: 14px; margin: 5px 0;">{{ number_format($sale->currier_amount, 2)}}</h2>
            </td>
        </tr>
    @endif

    @php
        $courier = $sale->delivery_charge_payer == 'customer' ? floatval($sale->currier_amount ?? $sale->delivery_charge ?? 0) : 0;
        $current_invoice_total = $sale->total_sale_amount - $sale->invoice_discount - $sale->special_discount + $sale->invoice_tax + $sale->cod_amount + $courier;
        $total_payable = $current_invoice_total + floatval($sale->previous_due ?? 0) - floatval($sale->advanced ?? 0);
        $live_due = $total_payable - $sale->paid;
    @endphp

    <tr class="tabletitle">
        <td class="Rate" colspan="2" style="text-align: right; padding-right: 10px;">
            <h2 style="font-size: 14px; margin: 5px 0;">Invoice Total</h2>
        </td>
        <td class="payment" style="text-align: right;">
            <h2 style="font-size: 14px; margin: 5px 0;"> {{ number_format($current_invoice_total, 2) }}</h2>
        </td>
    </tr>

    <tr class="tabletitle">
        <td class="Rate" colspan="2" style="text-align: right; padding-right: 10px;">
            <h2 style="font-size: 14px; margin: 5px 0;">Previous Due</h2>
        </td>
        <td class="payment" style="text-align: right;">
            <h2 style="font-size: 14px; margin: 5px 0;"> {{ number_format($sale->previous_due, 2)}}</h2>
        </td>
    </tr>

    <tr class="tabletitle">
        <td colspan="2" class="Rate" style="text-align: right; padding-right: 10px; border-top: 1px solid #000;">
            <h2 style="font-size: 16px; margin: 5px 0;">Total Payable</h2>
        </td>
        <td class="payment" style="text-align: right; border-top: 1px solid #000;">
            <h2 style="font-size: 16px; margin: 5px 0;">
                {{ number_format($total_payable, 2)}}
            </h2>
        </td>
    </tr>

    <tr class="tabletitle">
        <td class="Rate" colspan="2" style="text-align: right; padding-right: 10px;">
            <h2 style="font-size: 14px; margin: 5px 0;">Given Amount</h2>
        </td>
        <td class="payment" style="text-align: right;">
            <h2 style="font-size: 14px; margin: 5px 0;"> {{ number_format($sale->paid, 2)}}</h2>
        </td>
    </tr>

    <tr class="tabletitle">
        <td class="Rate" colspan="2" style="text-align: right; padding-right: 10px;">
            <h2 style="font-size: 14px; margin: 5px 0; font-weight: bold;">Due Amount</h2>
        </td>
        <td class="payment" style="text-align: right;">
            <h2 style="font-size: 14px; margin: 5px 0; font-weight: bold;"> 
                {{ $live_due > 0 ? number_format($live_due, 2) : '0.00' }}
            </h2>
        </td>
    </tr>

    @if($sale->change_amount > 0)
    <tr class="tabletitle">
        <td class="Rate" colspan="2" style="text-align: right; padding-right: 10px;">
            <h2 style="font-size: 14px; margin: 5px 0; font-weight: bold;">Change Return</h2>
        </td>
        <td class="payment" style="text-align: right;">
            <h2 style="font-size: 14px; margin: 5px 0; font-weight: bold;"> 
                {{ number_format($sale->change_amount, 2) }}
            </h2>
        </td>
    </tr>
    @endif

    <tr class="tabletitle">
        <td class="Rate" colspan="2" style="text-align: right; padding-right: 10px;">
            <h2 style="font-size: 14px; margin: 5px 0;">Current Balance</h2>
        </td>
        <td class="payment" style="text-align: right;">
            <h2 style="font-size: 14px; margin: 5px 0;"> 
                {{ $live_due > 0 ? number_format($live_due, 2) : '0.00' }}
            </h2>
        </td>
    </tr>
</table>
                </div>
                <!--End Table-->

                {{-- <center >
                    <div class="">
                        @if ($invoiceBarcode)
                            <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG(1234567, "C128") }}" alt="barcode" style="width: 140px; height: 60px;" />
                        @endif

                        <h2 style="font-size: 14px; margin-top: 20px;">{{ config('app.thanks_message_in_sale_invoice') ?? 'Thanks For Coming' }}</h2>
                    </div>
                </center> --}}
            </div>
        </div>



    </body>

<script>
    window.print()
</script>

</html>
