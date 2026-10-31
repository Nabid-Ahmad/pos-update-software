<table>
    <thead>
        <tr>
            <th>Sl.</th>
            <th>Product Code</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Brand</th>
            <th style="text-align: right">Product Cost</th>
            <th style="text-align: right">Sale Amount</th>
            <th style="text-align: right">Profit Amount</th>
        </tr>
    </thead>

    <tbody>
        @isset($products)
            @php
                $totalSaleAmount = 0;
                $totalCostAmount = 0;
                $sl = 1;
            @endphp
            @foreach($products as $product)
                @php
                    $totalSaleAmount += $product->total_sale_price;
                    $totalCostAmount += $product->total_product_cost;
                @endphp
                <tr>
                    <td>{{ $sl++ }}</td>
                    <td>{{ $product->product_code }}</td>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->category->category_name ?? '' }}</td>
                    <td>{{ optional($product->brand)->name ?? '' }}</td>
                    <td style="text-align: right">{{ number_format($product->total_product_cost ?? 0, 2) }}</td>
                    <td style="text-align: right">{{ number_format($product->total_sale_price ?? 0, 2) }}</td>
                    <td style="text-align: right">{{ number_format($product->total_sale_price - $product->total_product_cost ?? 0, 2) }}</td>
                </tr>
            @endforeach
            <tr style="font-weight: bold;">
                <td colspan="5">Total:</td>
                <td style="text-align: right">{{ number_format($totalCostAmount, 2) }}</td>
                <td style="text-align: right">{{ number_format($totalSaleAmount, 2) }}</td>
                <td style="text-align: right">{{ number_format($totalSaleAmount - $totalCostAmount, 2) }}</td>
            </tr>
        @endisset
    </tbody>
</table>
