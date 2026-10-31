<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5; background-color: #f9f9f9; padding: 20px; }
        .greeting { margin-bottom: 20px; font-size: 16px; color: #333; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #fff; border: 1px solid #ddd; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; color: #0056b3; }
        .details { margin-bottom: 20px; }
        .details p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row th, .total-row td { font-weight: bold; border-top: 2px solid #333; }
        .in-words { margin-top: 10px; font-weight: bold; font-style: italic; color: #555; }
        .footer { text-align: center; font-size: 12px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <!-- Greeting Message (Outside Invoice Container) -->
    <div class="greeting">
        <p>Dear <strong>{{ optional($sale->sale_customer)->name ?? 'Customer' }}</strong>,</p>
        <p>Thank you for your business. Please find your invoice details below. A PDF copy of this invoice has also been attached for your records.</p>
    </div>

    <!-- The Invoice Box -->
    <div class="container">
        <div class="header">
            <h1>{{ $company->name }}</h1>
            <p>{{ $company->address }}<br>Phone: {{ $company->phone }}</p>
        </div>

        <div class="details">
            <p><strong>Invoice Number:</strong> {{ $sale->sale_invoice_id }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') }}</p>
            <p><strong>Customer:</strong> {{ optional($sale->sale_customer)->name }}</p>
            @if(optional($sale->sale_customer)->phone)
                <p><strong>Phone:</strong> {{ $sale->sale_customer->phone }}</p>
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 10%;">SL</th>
                    <th>Product</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->sale_details as $key => $item)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td>{{ optional($item->product)->product_name }}</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>Sub Total</strong></td>
                    <td class="text-right">{{ number_format($sale->sale_details->sum(function($item) { return $item->quantity * $item->unit_price; }), 2) }}</td>
                </tr>
                @if($sale->invoice_tax > 0)
                <tr>
                    <td colspan="4" class="text-right"><strong>Tax</strong></td>
                    <td class="text-right">{{ number_format($sale->invoice_tax, 2) }}</td>
                </tr>
                @endif
                @if($sale->invoice_discount > 0)
                <tr>
                    <td colspan="4" class="text-right"><strong>Discount</strong></td>
                    <td class="text-right">{{ number_format($sale->invoice_discount, 2) }}</td>
                </tr>
                @endif
                @if($sale->special_discount > 0)
                <tr>
                    <td colspan="4" class="text-right"><strong>Special Discount</strong></td>
                    <td class="text-right">{{ number_format($sale->special_discount, 2) }}</td>
                </tr>
                @endif
                @if($sale->delivery_charge_payer == 'customer' && floatval($sale->currier_amount ?? $sale->delivery_charge) > 0)
                <tr>
                    <td colspan="4" class="text-right"><strong>Delivery Charge</strong></td>
                    <td class="text-right">{{ number_format(floatval($sale->currier_amount ?? $sale->delivery_charge), 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>Total Payable</strong></td>
                    <td class="text-right">{{ number_format($sale->payable_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right"><strong>Paid Amount</strong></td>
                    <td class="text-right">{{ number_format($sale->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right"><strong>Due Amount</strong></td>
                    <td class="text-right">{{ number_format($sale->payable_amount - $sale->paid_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Amount In Words -->
        <div class="in-words">
            Amount In Words: {{ ucwords(str_replace('-', ' ', inWords($sale->payable_amount))) }} Only
        </div>

        <div class="footer">
            <p>This is a system generated invoice.</p>
        </div>
    </div>
</body>
</html>
