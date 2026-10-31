<table>
    <thead>
        <tr>
            <th>Sl.</th>
            <th>Date</th>
            <th>Invoice ID</th>
            <th>Customer</th>
            <th style="text-align: right">Purchase Price</th>
            <th style="text-align: right">Sale Price</th>
            <th style="text-align: right">Discount</th>
            <th style="text-align: right">Profit</th>
        </tr>
    </thead>

    <tbody>
        @isset($sales)
            @php
                $totalCost = 0;
                $totalPrice = 0;
                $totalDiscount = 0;
                $totalProfit = 0;
                $sl = 1;
            @endphp
            @foreach($sales as $sales_info)
                @php
                    $cost = $sales_info->total_purchase_cost ?? 0;
                    $sale = $sales_info->total_sale_price ?? 0;
                    $invoice_discount = floatval($sales_info->invoice_discount ?? 0) + floatval($sales_info->special_discount ?? 0);
                    $profit = $sale - $invoice_discount - $cost;

                    $totalCost += $cost;
                    $totalPrice += $sale;
                    $totalDiscount += $invoice_discount;
                    $totalProfit += $profit;
                @endphp
                <tr>
                    <td>{{ $sl++ }}</td>
                    <td>{{ \Carbon\Carbon::parse($sales_info->sale_date)->format('d-m-Y') }}</td>
                    <td>{{ $sales_info->invoiceId }}</td>
                    <td>{{ $sales_info->sale_customer->name ?? '' }}</td>
                    <td style="text-align: right">{{ number_format($cost, 2) }}</td>
                    <td style="text-align: right">{{ number_format($sale, 2) }}</td>
                    <td style="text-align: right">{{ number_format($invoice_discount, 2) }}</td>
                    <td style="text-align: right">{{ number_format($profit, 2) }}</td>
                </tr>
            @endforeach
            <tr style="font-weight: bold;">
                <td colspan="4">Total:</td>
                <td style="text-align: right">{{ number_format($totalCost, 2) }}</td>
                <td style="text-align: right">{{ number_format($totalPrice, 2) }}</td>
                <td style="text-align: right">{{ number_format($totalDiscount, 2) }}</td>
                <td style="text-align: right">{{ number_format($totalProfit, 2) }}</td>
            </tr>
        @endisset
    </tbody>
</table>
