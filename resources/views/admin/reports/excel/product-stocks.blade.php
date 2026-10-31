<table>
    <thead>
        <tr>
            <th colspan="11" style="text-align: center; font-size: 16px; font-weight: bold;">
                {{ optional(auth()->user()->company)->name }}
            </th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center; font-size: 12px;">
                {{ optional(auth()->user()->company)->address }}
            </th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center; font-size: 12px;">
                Phone: {{ optional(auth()->user()->company)->phone }}, Email: {{ optional(auth()->user()->company)->email }}
            </th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center; font-size: 14px; font-weight: bold;">
                Product Wise Stock Report
            </th>
        </tr>
        <tr>
            <th colspan="11"></th>
        </tr>
        <tr>
            <th>Sl</th>
            <th>Product Code</th>
            <th>Product Name</th>
            <th>Opening Qty</th>
            <th>Purchased Qty</th>
            <th>Free Qty</th>
            <th>Sold Qty</th>
            <th>Wastage Qty</th>
            <th>Available Qty</th>
            <th>Product Cost</th>
            <th>Value</th>
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
                <td>{{ $key + 1 }}</td>
                <td>{{ $product->product_code }}</td>
                <td>{{ $product->product_name }}</td>
                <td>{{ (float)$opening_quantity }}</td>
                <td>{{ (float)$purchase_quantity }}</td>
                <td>{{ (float)$free_quantity }}</td>
                <td>{{ (float)$sold_quantity }}</td>
                <td>{{ (float)$wastage_quantity }}</td>
                <td>{{ (float)$available_quantity }}</td>
                <td>{{ (float)$product->product_cost }}</td>
                <td>{{ (float)$subtotal }}</td>
            </tr>
        @endforeach

        <tr>
            <th colspan="3" style="text-align: right; font-weight: bold;">Total:</th>
            <th style="font-weight: bold;">{{ (float)$total_opening_quantity }}</th>
            <th style="font-weight: bold;">{{ (float)$total_purchase_quantity }}</th>
            <th style="font-weight: bold;">{{ (float)$total_free_quantity }}</th>
            <th style="font-weight: bold;">{{ (float)$total_sold_quantity }}</th>
            <th style="font-weight: bold;">{{ (float)$total_wastage_quantity }}</th>
            <th style="font-weight: bold;">{{ (float)$total_available_quantity }}</th>
            <th></th>
            <th style="font-weight: bold;">{{ (float)$subtotal_price }}</th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: right; font-weight: bold;">Grand Total:</th>
            <th style="font-weight: bold;">{{ (float)$total_product_price }}</th>
        </tr>
    </tbody>
</table>
