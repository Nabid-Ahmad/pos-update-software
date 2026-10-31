<table>
    <thead>
    <tr>
        <th colspan="13" style="text-align: center; font-size: 16px; font-weight: bold;">
            {{ optional(auth()->user()->company)->name }}
        </th>
    </tr>
    <tr>
        <th colspan="13" style="text-align: center; font-size: 12px;">
            {{ optional(auth()->user()->company)->address }}
        </th>
    </tr>
    <tr>
        <th colspan="13" style="text-align: center; font-size: 12px;">
            Phone: {{ optional(auth()->user()->company)->phone }}, Email: {{ optional(auth()->user()->company)->email }}
        </th>
    </tr>
    <tr>
        <th colspan="13" style="text-align: center; font-size: 14px; font-weight: bold;">
            Supplier Wise Stock Report
        </th>
    </tr>
    @if ($supplier)
    <tr>
        <th colspan="13" style="text-align: center; font-size: 12px;">
            Supplier: {{$supplier->name ?? '' }} ({{$supplier->company_name ?? '' }}) | {{$supplier->phone ?? '' }}, {{$supplier->email ?? '' }}
        </th>
    </tr>
    @endif
    <tr>
        <th colspan="13"></th>
    </tr>
    <tr>
        <th>Sl</th>
        <th>Product Code</th>
        <th>Product Name</th>
        <th>Category</th>
        <th>Supplier</th>
        <th>Opening Qty</th>
        <th>Purchase Quantity</th>
        <th>Free Qty</th>
        <th>Sold Qty</th>
        <th>Damaged Qty</th>
        <th>Available Qty</th>
        <th>Product Cost</th>
        <th>Value</th>
    </tr>
    </thead>
    <tbody>
    @php $total = 0; $totalProductPrice = 0; @endphp
    @foreach($stocks as $key => $stock)
        @php 
            $value = (optional($stock->stock_product)->product_cost * $stock->available_quantity);
            $total += $value;
            $totalProductPrice += $value;
        @endphp
        <tr>
            <td>{{ $key+1 }}</td>
            <td>{{ optional($stock->product)->product_code }}</td>
            <td>{{ optional($stock->product)->product_name }}</td>
            <td>{{ optional(optional($stock->product)->category_name)->category_name }}</td>
            <td>{{ optional(optional($stock->product)->supplier)->name ?? '' }}</td>
            <td>{{ (float)$stock->opening_quantity }}</td>
            <td>{{ (float)$stock->purchased_quantity }}</td>
            <td>{{ (float)$stock->free_quantity }}</td>
            <td>{{ (float)$stock->sold_quantity }}</td>
            <td>{{ (float)$stock->wastage_quantity }}</td>
            <td>{{ (float)$stock->available_quantity }}</td>
            <td>{{ (float)optional($stock->stock_product)->product_cost }}</td>
            <td>{{ (float)$value }}</td>
        </tr>
    @endforeach
    <tr>
        <th colspan="12" align="right"><strong>Grand Total:</strong></th>
        <th><strong>{{ (float)$totalProductPrice }}</strong></th>
    </tr>
    </tbody>
</table>
