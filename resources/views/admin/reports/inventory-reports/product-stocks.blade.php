@extends('admin.master')
@section('title', ' - Product Wise Stock Report')

@push('style')
    <style>
        @media print {
            tr {
                page-break-inside: avoid;
            }
            .d-none {
                display: block !important;
            }
            .d-print {
                display: block !important;
            }
        }
        .d-print {
            display: none;
        }
        .cost-val-cell, th.text-right, td.text-right {
            padding-right: 15px !important;
        }
    </style>
@endpush
@section('content')

    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <div class="row">
                            <div class="col-md-12 mx-auto">

                                <div class="table-responsive">
                                    <form action="" method="get">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td width="20%" class="no-print ">
                                                    <label class="control-label">Brand</label>
                                                    <select name="brand_id" class="form-control select2">
                                                        <option value=""> All Brand</option>
                                                        @foreach ($brands as $id => $name)
                                                            <option value="{{ $id }}" {{ request('brand_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>

                                                <td width="20%" class="no-print ">
                                                    <label class="control-label">Category</label>
                                                    <select name="category_id" id="fk_category_id" class="form-control select2">
                                                        <option value=""> All Category</option>
                                                        @foreach ($categories as $key => $category)
                                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>

                                                @if ($settings->where('title', 'Product Subcategory')->where('options', 'yes')->count() > 0)
                                                    <td width="20%" class="no-print ">
                                                        <label class="control-label">Sub Category</label>
                                                        <select name="fk_sub_category_id" id="fk_sub_category_id" class="form-control select2">
                                                            <option value="">Select</option>
                                                            @php $subcategories = optional($categories->where('id', request('category_id'))->first())->subcategories ?? [] @endphp
                                                            @foreach ($subcategories as $subcategory)
                                                                <option {{ request('fk_sub_category_id') == $subcategory->id ? 'selected' : ''  }} value="{{ $subcategory->id }}">{{ $subcategory->sub_category_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                @endif

                                                <td width="20%" class="no-print ">
                                                    <label class="control-label">Warehouse</label>
                                                    <select name="warehouse_id" class="form-control select2">
                                                        <option value="" > Showroom</option>
                                                        <option value="all" {{ request('warehouse_id') == 'all' ? 'selected' : '' }}> All Stock</option>
                                                        @foreach ($warehouses as $id => $name)
                                                            <option value="{{ $id }}" {{ request('warehouse_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>

                                                <td width="20%" class="no-print ">
                                                    <label class="control-label">Product Name</label>
                                                    <select name="product_id" class="form-control select2">
                                                        <option value="" > All </option>
                                                        @foreach ($selectproducts as $id => $name)
                                                            <option value="{{ $id }}" {{ request('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>

                                                <td class="no-print" style="width: auto;">
                                                    <div class="form-group" style="margin-top: 26px; display: flex; gap: 10px;">
                                                        <button class="btn btn-primary"><i class="fa fa-check"></i> Check</button>
                                                        <button type="button" class="btn btn-warning" onclick="event.preventDefault(); if(confirm('Are you sure you want to rebuild all stock data? This might take a few seconds.')){ document.getElementById('sync-stock-form').submit(); }"><i class="fa fa-refresh"></i> Sync Stock</button>
                                                    </div>
                                                </td>
                                            </tr>

                                        </table>
                                    </form>
                                    <form id="sync-stock-form" action="{{ route('product.stock.sync') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div style="width: 90% !important; margin-left: 5%">
                                <table id="stock_table" class="table table-bordered table-sm" style="border: none !important;">
                                    <thead style="border: none !important;">
                                        <tr>
                                            <th colspan="11" class="text-center py-2" style="border: none !important;">
                                                @include('partials._print_header')
                                                <h4>Product Wise Stock Report</h4>
                                                <h5>
                                                    @php $showCount = 0 @endphp
                                                    @if (request('brand_id') != '')
                                                        Brand: {{ $brands[request('brand_id')] }}
                                                        @php $showCount++ @endphp
                                                    @endif

                                                    @if (request('category_id') != '')
                                                        @if($showCount > 0) , @endif
                                                        Category: {{ $categories->where('id', request('category_id'))->first()->category_name }}
                                                        @php $showCount++ @endphp
                                                    @endif

                                                    @if (request('fk_sub_category_id') != '')
                                                        @if($showCount > 0) , @endif
                                                        Subcategory: {{ optional(optional($subcategories)->where('id', request('fk_sub_category_id'))->first())->sub_category_name }}
                                                        @php $showCount++ @endphp
                                                    @endif

                                                    @if (request('warehouse_id') != '' && request('warehouse_id') != 'all')
                                                        @if($showCount > 0) , @endif
                                                        Warehouse: {{ $warehouses[request('warehouse_id')] }}
                                                        @php $showCount++ @endphp
                                                    @endif
                                                </h5>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th>Sl.</th>
                                            <th width="12%">Product Code</th>
                                            <th>Product Name</th>
                                            <th width="8%" class="text-center">Opening Qty</th>
                                            <th width="8%" class="text-center">Purchased Qty</th>
                                            <th width="8%" class="text-center" style="color: green;">Free Qty</th>
                                            <th width="8%" class="text-center">Sold Qty</th>
                                            <th width="8%" class="text-center" style="color: red;">Wastage Qty</th>
                                            <th width="8%" class="text-center">Available Qty</th>
                                            <th width="10%" class="text-right">Product Cost</th>
                                            <th width="10%" class="text-right">Value</th>
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
                                                <td>{{ $key + $products->firstItem() }}</td>
                                                <td>{{ $product->product_code }}</td>
                                                <td>{{ $product->product_name }}</td>
                                                <td class="text-center qty-cell">{{ $opening_quantity }}</td>
                                                <td class="text-center qty-cell">{{ $purchase_quantity }}</td>
                                                <td class="text-center qty-cell" style="color: green;">{{ $free_quantity }}</td>
                                                <td class="text-center qty-cell">{{ $sold_quantity }}</td>
                                                <td class="text-center qty-cell" style="color: red;">{{ $wastage_quantity }}</td>
                                                <td class="text-center qty-cell">
                                                    @if ($product->has_serial == 1 && $available_quantity > 0)
                                                        <a href="{{ route('serial-product.stocks') }}?product_id={{ $product->id }}&warehouse_id={{ request('warehouse_id') }}">{{ $available_quantity }}</a>
                                                    @else
                                                        {{ $available_quantity }}
                                                    @endif
                                                </td>
                                                <td class="text-right cost-val-cell">{{ number_format($product->product_cost, 2) }}</td>
                                                <td class="text-right cost-val-cell">{{ number_format($subtotal, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <th colspan="3">Total this page:</th>
                                            <th class="text-center qty-cell">{{ $total_opening_quantity }}</th>
                                            <th class="text-center qty-cell">{{ $total_purchase_quantity }}</th>
                                            <th class="text-center qty-cell" style="color: green;">{{ $total_free_quantity }}</th>
                                            <th class="text-center qty-cell">{{ $total_sold_quantity }}</th>
                                            <th class="text-center qty-cell" style="color: red;">{{ $total_wastage_quantity }}</th>
                                            <th class="text-center qty-cell">{{ $total_available_quantity }}</th>
                                            <th></th>
                                            <th class="text-right cost-val-cell">{{ number_format($subtotal_price, 2) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="10">Grand Total:</th>
                                            <th class="text-right cost-val-cell">{{ number_format($total_product_price, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>

                                <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; margin-bottom: 15px;">
                                    <div style="display: flex; gap: 12px; align-items: center;">
                                        <style>
                                            .download-icon-btn {
                                                transition: transform 0.2s ease, filter 0.2s ease;
                                                cursor: pointer;
                                                display: inline-block;
                                                border: none;
                                                background: none;
                                                padding: 0;
                                                vertical-align: middle;
                                            }
                                            .download-icon-btn:hover {
                                                transform: scale(1.1);
                                                filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));
                                            }
                                        </style>

                                        <!-- Excel Icon -->
                                        @php
                                            $excelUrl = request()->fullUrlWithQuery(['print' => 'excel']);
                                        @endphp
                                        <a href="{{ $excelUrl }}" class="download-icon-btn" title="Download Excel">
                                            @include('admin.reports.includes.excel_icon')
                                        </a>

                                        <!-- PDF Icon -->
                                        @php
                                            $pdfUrl = route('product.stocks.pdf') . '?' . http_build_query(request()->all());
                                        @endphp
                                        <a href="{{ $pdfUrl }}" class="download-icon-btn ml-2" title="Download PDF">
                                            @include('admin.reports.includes.pdf_icon')
                                        </a>


                                    </div>
                                    <div>
                                        @include('admin.includes.pagination', ['data' => $products])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <input type="hidden" class="subcategory-url" value="{{ route('subcategories.index') }}">
@endsection


@section('footer-script')
    <script type="text/javascript" src="{{ asset('assets/admin/js/plugins/jquery.dataTables.min.js')}}"></script>

    <script type="text/javascript">
        $('.select2').select2();

        function downloadExcelDirect() {
            var originalTable = document.getElementById('stock_table');
            if (!originalTable) return;
            
            if (typeof XLSX === 'undefined' || !XLSX.utils.book_append_sheet) {
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js';
                script.onload = performExport;
                document.head.appendChild(script);
            } else {
                performExport();
            }

            function performExport() {
                var clonedTable = originalTable.cloneNode(true);

                var dPrintNoneElements = clonedTable.querySelectorAll('.d-print-none, .no-print');
                dPrintNoneElements.forEach(function(el) { el.parentNode.removeChild(el); });

                var titleRow = clonedTable.querySelector('thead tr');
                if (titleRow && titleRow.querySelector('td[colspan], th[colspan]')) {
                    var titleCell = titleRow.querySelector('td[colspan], th[colspan]');
                    
                    var companyName = titleCell.querySelector('h3') ? titleCell.querySelector('h3').innerText : '';
                    var address = titleCell.querySelectorAll('h5')[0] ? titleCell.querySelectorAll('h5')[0].innerText : '';
                    var contact = titleCell.querySelectorAll('h5')[1] ? titleCell.querySelectorAll('h5')[1].innerText : '';
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Product Wise Stock Report';
                    var filterInfo = titleCell.querySelectorAll('h5')[2] ? titleCell.querySelectorAll('h5')[2].innerText.replace(/\s+/g, ' ').trim() : '';

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 11;

                    var newRowsHtml = `
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                        ${filterInfo ? `<tr><td colspan="${colspanVal}" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${filterInfo}</td></tr>` : ''}
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                        <tr><td colspan="${colspanVal}" style="border: none; height: 10px;"></td></tr>
                    `;
                    
                    var tempDiv = document.createElement('tbody');
                    tempDiv.innerHTML = newRowsHtml;
                    var parent = titleRow.parentNode;
                    while (tempDiv.firstChild) { parent.insertBefore(tempDiv.firstChild, titleRow); }
                    parent.removeChild(titleRow);
                }

                var sheet = XLSX.utils.table_to_sheet(clonedTable, { raw: false });

                var range = XLSX.utils.decode_range(sheet['!ref']);
                var colWidths = [
                    { wch: 6 },   // Sl.
                    { wch: 15 },  // Product Code
                    { wch: 30 },  // Product Name
                    { wch: 12 },  // Opening Qty
                    { wch: 14 },  // Purchased Qty
                    { wch: 12 },  // Free Qty
                    { wch: 12 },  // Sold Qty
                    { wch: 14 },  // Wastage Qty
                    { wch: 14 },  // Available Qty
                    { wch: 14 },  // Product Cost
                    { wch: 14 }   // Value
                ];
                sheet['!cols'] = colWidths;

                var headerRowCount = filterInfo ? 6 : 5;

                for (var R = range.s.r; R <= range.e.r; ++R) {
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        var cell_ref = XLSX.utils.encode_cell({c: C, r: R});
                        var cell = sheet[cell_ref];
                        if (cell && cell.v !== undefined) {
                            
                            cell.s = { font: { name: 'Arial', sz: 11 }, alignment: { vertical: "center" } };

                            if (R < headerRowCount - 1) {
                                cell.s.alignment.horizontal = "center";
                                cell.s.font.bold = true;
                                if (R === 0) cell.s.font.sz = 14;
                            } else if (R === headerRowCount - 1) {
                                // empty
                            } else if (R === headerRowCount || cell.v.toString().includes('Total')) {
                                cell.s.font.bold = true;
                                cell.s.alignment.horizontal = "center";
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if(C >= 9) cell.s.alignment.horizontal = "right";
                            } else {
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C === 0) cell.s.alignment.horizontal = "left";
                                else if (C >= 9) cell.s.alignment.horizontal = "right";
                                else cell.s.alignment.horizontal = "center";
                            }

                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'Sl.' || valStr === 'Grand Total:' || valStr === 'Total this page:') continue;
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal)) {
                                cell.t = 'n';
                                cell.v = numVal;
                                if (C === 0) cell.z = '0'; 
                                if (C >= 3 && C <= 8) cell.z = '0.00'; 
                                if (C >= 9 && C <= 10) cell.z = '#,##0.00'; 
                            }
                        }
                    }
                }

                var wb = XLSX.utils.book_new();
                var sheetName = reportTitle.length > 30 ? reportTitle.substring(0, 30) : reportTitle;
                XLSX.utils.book_append_sheet(wb, sheet, sheetName);
                
                var safeFileName = reportTitle.replace(/[^a-zA-Z0-9]/g, '_') + '_' + new Date().toISOString().slice(0,10) + '.xlsx';
                XLSX.writeFile(wb, safeFileName);
            }
        }

        function downloadPDFDirect() {
            var element = document.getElementById('stock_table');
            if (!element) return;

            var iframe = document.createElement('iframe');
            iframe.style.position = 'absolute';
            iframe.style.top = '-10000px';
            iframe.style.left = '-10000px';
            iframe.style.width = '1200px';
            iframe.style.height = '1000px';
            document.body.appendChild(iframe);

            var tableHTML = element.outerHTML;
            var doc = iframe.contentWindow.document;
            doc.open();
            doc.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <style>
                        body { font-family: Arial, sans-serif; font-size: 11px; background: #fff; margin: 0; padding: 20px; color: #000; }
                        table { width: 100%; border-collapse: collapse; background: #fff; }
                        th, td { border: 1px solid #dee2e6 !important; padding: 6px 8px; vertical-align: middle; color: #000; }
                        th { background-color: #f8f9fa; font-weight: bold; }
                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        .text-left { text-align: left; }
                        h3, h4, h5, p { text-align: center; margin: 5px 0; color: #000; }
                        .no-print, .d-print-none { display: none !important; }
                        .d-print { display: block !important; }
                    </style>
                </head>
                <body>
                    <div id="pdf-content">
                        ${tableHTML}
                    </div>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"><\/script>
                    <script>
                        window.onload = function() {
                            var table = document.querySelector('table');
                            if(table) table.removeAttribute('style');
                            var cells = document.querySelectorAll('td, th');
                            cells.forEach(function(c) { c.removeAttribute('style'); });

                            var opt = {
                                margin:       [0.5, 0.5, 0.5, 0.5],
                                filename:     'Product_Wise_Stock_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_product', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_product') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }

        $("#submitBtn").on('click', function () {

            $('#stock_table tbody').empty();
            var product_name = $("#product_name").val();
            var purchase_date = $("#purchase_date").val();
            var sale_date = $("#sale_date").val();
            var url_data = 'id=' + product_name + '&purchase=' + purchase_date + '&sale=' + sale_date;
            $.ajax({

                url: "{{ url('reports/get_json_stock') }}",
                type: "GET",
                data: {id: product_name, purchase_date: purchase_date, sale_date: sale_date},
                success: function (res) {
                    $.each(res, function (key, value) {
                        var total_sold = 0;
                        if (value['sales_details'] != null) {
                            total_sold = value['sales_details']['total_sold'];
                        }
                        var tr = '<tr>' +
                            '<td>' + value['product']['product_code'] + '</td>' +
                            '<td>' + value['product']['product_name'] + '</td>' +
                            '<td>' + value['total_purchased'] + '</td>' +
                            '<td>' + total_sold + '</td>' +
                            '<td>' + value['product_stock']['available_quantity'] + '</td>' +
                            '</tr>';
                        $('#stock_table tbody').append(tr);
                    });
                }
            });
        });


        $('#fk_category_id').change(function () {

            var categoryId = $(this).val();
            if (categoryId) {

                $.ajax({
                    type: "GET",
                    url: $('.subcategory-url').val() + '/' + categoryId,
                    success: function (res) {
                        if (res) {
                            $("#fk_sub_category_id").empty();
                            $("#fk_sub_category_id").append('<option value="">Salect One</option>');
                            $(res).each(function (index, item) {
                                $("#fk_sub_category_id").append('<option value="' + item.id + '">' + item.sub_category_name + '</option>');
                            });
                        } else {
                            $("#fk_sub_category_id").empty();
                        }

                    }
                });
            } else {
                $("#fk_sub_category_id").empty();
            }
        });
    </script>

    @include('admin.includes.date_field')

    <script type="text/javascript" src="{{asset('jq/select2Loads.js')}}"></script>
    <script>
        select2Loads({
            selector: '.dynamic_product',
            url: "{{ url('product/products') }}",
        })
    </script>
@endsection