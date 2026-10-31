<table>
    <thead>
    <tr>
        <th colspan="9" align="center">
            <strong>Product List</strong>
        </th>
    </tr>
    <tr>
        <th>Sl</th>
        <th>Supplier</th>
        <th>Product Name</th>
        <th>Product Code</th>
        <th>Barcode</th>
        <th>Unit</th>
        <th>Serial</th>
        <th>Purchase Price</th>
        <th>Sale Price</th>
    </tr>
    </thead>
    <tbody>
    @foreach($products as $key => $product)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>{{ optional($product->supplier)->name }}</td>
            <td>{{ $product->product_name }}</td>
            <td>{{ $product->product_code }}</td>
            <td>{{ optional($product->barcode)->barcode_number }}</td>
            <td>{{ optional($product->product_unit)->name }}</td>
            <td>{{ $product->has_serial == 1 ? 'Yes' : 'No' }}</td>
            <td>{{ $product->product_cost }}</td>
            <td>{{ $product->product_price }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
