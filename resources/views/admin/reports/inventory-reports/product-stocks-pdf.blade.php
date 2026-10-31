<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Wise Stock Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8px; color: #000; background: #fff; }
        h3 { font-size: 13px; text-align: center; margin-bottom: 2px; }
        h5 { font-size: 9px; text-align: center; margin-bottom: 2px; font-weight: normal; }
        h4 { font-size: 11px; text-align: center; margin: 4px 0; }
        p  { font-size: 9px; text-align: center; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #ccc; padding: 3px 4px; vertical-align: middle; }
        thead th { background-color: #f0f0f0; font-weight: bold; text-align: center; font-size: 8px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .grand-total-row th { background-color: #e8e8e8; font-size: 9px; }
        .total-row td, .total-row th { background-color: #f5f5f5; font-weight: bold; }
    </style>
</head>
<body>

    <h3>{{ optional(auth()->user()->company)->name }}</h3>
    <h5>{{ optional(auth()->user()->company)->address }}</h5>
    <h5>Phone: {{ optional(auth()->user()->company)->phone }}, Email: {{ optional(auth()->user()->company)->email }}</h5>
    <h4>Product Wise Stock Report</h4>

    <table>
        <thead>
            <tr>
                <th width="4%">Sl</th>
                <th width="10%">Product Code</th>
                <th width="20%">Product Name</th>
                <th width="7%">Opening Qty</th>
                <th width="7%">Purchased Qty</th>
                <th width="7%">Free Qty</th>
                <th width="7%">Sold Qty</th>
                <th width="7%">Wastage Qty</th>
                <th width="7%">Available Qty</th>
                <th width="10%">Product Cost</th>
                <th width="14%">Value</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_opening_quantity     = 0;
                $total_purchase_quantity    = 0;
                $total_free_quantity        = 0;
                $total_sold_quantity        = 0;
                $total_wastage_quantity     = 0;
                $total_available_quantity   = 0;
                $subtotal_price             = 0;
            @endphp
            @foreach ($products as $key => $product)
                @php
                    $total_opening_quantity  += $opening_quantity  = $product->opening_quantity ?? 0;
                    $total_purchase_quantity += $purchase_quantity = $product->purchased_quantity ?? 0;
                    $total_free_quantity     += $free_quantity     = $product->free_quantity ?? 0;
                    $total_sold_quantity     += $sold_quantity     = $product->sold_quantity ?? 0;
                    $total_wastage_quantity  += $wastage_quantity  = $product->wastage_quantity ?? 0;
                    $total_available_quantity += $available_quantity = ($product->available_quantity ?? 0);
                    $total_received = $opening_quantity + $purchase_quantity + $free_quantity;
                    $paid_ratio = $total_received > 0 ? ($opening_quantity + $purchase_quantity) / $total_received : 0;
                    $subtotal = $available_quantity * $paid_ratio * $product->product_cost;
                    $subtotal_price          += $subtotal;
                @endphp

                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td>{{ $product->product_code }}</td>
                    <td>{{ $product->product_name }}</td>
                    <td class="text-center">{{ $opening_quantity }}</td>
                    <td class="text-center">{{ $purchase_quantity }}</td>
                    <td class="text-center">{{ $free_quantity }}</td>
                    <td class="text-center">{{ $sold_quantity }}</td>
                    <td class="text-center">{{ $wastage_quantity }}</td>
                    <td class="text-center">{{ $available_quantity }}</td>
                    <td class="text-right">{{ number_format($product->product_cost, 2) }}</td>
                    <td class="text-right">{{ number_format($subtotal, 2) }}</td>
                </tr>
            @endforeach

            <tr class="total-row">
                <th colspan="3" class="text-right">Total:</th>
                <th class="text-center">{{ $total_opening_quantity }}</th>
                <th class="text-center">{{ $total_purchase_quantity }}</th>
                <th class="text-center">{{ $total_free_quantity }}</th>
                <th class="text-center">{{ $total_sold_quantity }}</th>
                <th class="text-center">{{ $total_wastage_quantity }}</th>
                <th class="text-center">{{ $total_available_quantity }}</th>
                <th></th>
                <th class="text-right">{{ number_format($subtotal_price, 2) }}</th>
            </tr>
            <tr class="grand-total-row">
                <th colspan="10" class="text-right">Grand Total:</th>
                <th class="text-right">{{ number_format($total_product_price, 2) }}</th>
            </tr>
        </tbody>
    </table>

</body>
</html>
