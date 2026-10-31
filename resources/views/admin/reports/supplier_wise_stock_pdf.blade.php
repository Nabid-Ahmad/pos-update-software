<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Wise Stock Report</title>
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
    <h4>Supplier Wise Stock Report</h4>

    @if($supplier)
        <p>
            Supplier: <strong>{{ $supplier->name ?? '' }} ({{ $supplier->company_name ?? '' }})</strong><br>
            {{ $supplier->phone ?? '' }} {{ $supplier->email ?? '' }}
        </p>
    @endif

    @php $total = 0; @endphp

    <table>
        <thead>
            <tr>
                <th width="3%">Sl</th>
                <th width="9%">Product Code</th>
                <th width="18%">Product Name</th>
                <th width="9%">Category</th>
                <th width="13%">Supplier</th>
                <th width="7%">Opening Qty</th>
                <th width="7%">Purchase Qty</th>
                <th width="6%">Free Qty</th>
                <th width="6%">Sold Qty</th>
                <th width="7%">Damaged Qty</th>
                <th width="7%">Available Qty</th>
                <th width="7%">Product Cost</th>
                <th width="8%">Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stocks as $key => $stock)
                @php
                    $rowValue = floatval(optional($stock->stock_product)->product_cost) * floatval($stock->available_quantity);
                    $total += $rowValue;
                @endphp
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td>{{ optional($stock->product)->product_code }}</td>
                    <td>{{ optional($stock->product)->product_name }}</td>
                    <td>{{ optional(optional($stock->product)->category_name)->category_name }}</td>
                    <td>{{ optional(optional($stock->product)->supplier)->name ?? '' }}</td>
                    <td class="text-center">{{ $stock->opening_quantity }}</td>
                    <td class="text-center">{{ $stock->purchased_quantity }}</td>
                    <td class="text-center">{{ $stock->free_quantity }}</td>
                    <td class="text-center">{{ $stock->sold_quantity }}</td>
                    <td class="text-center">{{ $stock->wastage_quantity }}</td>
                    <td class="text-center">{{ $stock->available_quantity }}</td>
                    <td class="text-right">{{ number_format(optional($stock->stock_product)->product_cost, 2) }}</td>
                    <td class="text-right">{{ number_format($rowValue, 2) }}</td>
                </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="12" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($total, 2) }}</strong></td>
            </tr>
            <tr class="grand-total-row">
                <th colspan="12" class="text-right">Grand Total (Cost Price):</th>
                <th class="text-right">{{ number_format($totalProductPrice, 2) }}</th>
            </tr>
        </tbody>
    </table>

</body>
</html>
