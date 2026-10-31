@extends('admin.master')
@section('content')

    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <form action="{{ url('reports/date-product-reports') }}" method="get">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td width="10%">
                                                    {{--<label class="control-label"></label>--}}
                                                    {{--<h2><i class="fa fa-database"></i> Date Wise Product Report </h2>--}}
                                                </td>
                                                <td width="20%" class="no-print">
                                                    <label class="control-label">Product Name</label>
                                                    <select name="product" id="product"
                                                            class="form-control dynamic_product">
                                                        @foreach($products as $id => $product_name)
                                                            <option
                                                                    {{ request()->get('product') == $id ? 'selected' : '' }} value="{{ $id }}">{{ $product_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('product')
                                                    <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                                <td width="20%" class="no-print">
                                                    <label class="control-label">Date</label>
                                                    <input type="text" id="dateField" class="form-control dateField"
                                                           name="date" value="{{ request()->get('date') }}"
                                                           placeholder="Select Date" autocomplete="off">
                                                    @error('date')
                                                    <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                                <td width="15%" class="no-print">

                                                    <div class="form-group" style="margin-top: 26px;">
                                                        <button class="btn btn-primary" title="Check"><i
                                                                    class="fa fa-check"></i></button>
                                                        <a href="{{ url('reports/product_stock') }}"
                                                           class="btn btn-danger" onclick="refresh()" title="Refresh"><i
                                                                    class="fa fa-refresh"></i> </a>
                                                        <button type="button" class="btn btn-success" onclick="window
                                                        .print()"><i
                                                                    class="fa fa-print"></i>Print
                                                        </button>
                                                    </div>


                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                            {{--                            </div>--}}

                        </div>
                        <div class="table-responsive">
                            <div id="report-wrapper">
                            <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                <thead>
                                <tr>
                                    <td colspan="4" class="text-center py-2" style="border: none !important; display: none;">
                                        @include('partials._print_header')
                                        <h4>Date Wise Product Report</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <th>Product Name</th>
                                    <th>Product Purchased</th>
                                    <th>Product Sold</th>
                                </tr>
                                </thead>
                                <tbody>

                                @isset($purchaseQuantity)

                                    @php
                                        $product = \App\Product::where('id',request()->get('product'))->select('product_name')->first();
                                    @endphp
                                    <tr>
                                        <td>{{ request()->get('date') }}</td>
                                        <td>{{ $product->product_name }}</td>
                                        <td>{{ $purchaseQuantity->totalQuantity + $purchaseQuantity->totalFreeQuantity  }}</td>
                                        <td>{{ $salesQuantity->totalSealsQuantity ? : 0 }}</td>
                                    </tr>

                                @endisset


                                </tbody>
                            </table>
                            </div>

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

                                    <!-- PDF Icon -->
                                    <a href="javascript:void(0)" onclick="downloadPDFDirect()" class="download-icon-btn" title="Download PDF">
                                        @include('admin.reports.includes.pdf_icon')
                                    </a>

                                    <!-- Excel Icon -->
                                    <a href="javascript:void(0)" onclick="downloadExcelDirect()" class="download-icon-btn" title="Download Excel">
                                        @include('admin.reports.includes.excel_icon')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


@endsection

@section('footer-script')
    <script>

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

                var titleRow = clonedTable.querySelector('thead tr:first-child');
                if (titleRow && titleRow.querySelector('td[colspan], th[colspan]')) {
                    var titleCell = titleRow.querySelector('td[colspan], th[colspan]');
                    
                    var companyName = titleCell.querySelector('h3') ? titleCell.querySelector('h3').innerText : '';
                    var address = titleCell.querySelectorAll('h5')[0] ? titleCell.querySelectorAll('h5')[0].innerText : '';
                    var contact = titleCell.querySelectorAll('h5')[1] ? titleCell.querySelectorAll('h5')[1].innerText : '';
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Date Wise Product Report';

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 4;

                    var newRowsHtml = `
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
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
                    { wch: 15 },  // Date
                    { wch: 30 },  // Product Name
                    { wch: 20 },  // Product Purchased
                    { wch: 20 }   // Product Sold
                ];
                sheet['!cols'] = colWidths;

                for (var R = range.s.r; R <= range.e.r; ++R) {
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        var cell_ref = XLSX.utils.encode_cell({c: C, r: R});
                        var cell = sheet[cell_ref];
                        if (cell && cell.v !== undefined) {
                            
                            cell.s = { font: { name: 'Arial', sz: 11 }, alignment: { vertical: "center" } };

                            if (R < 4) {
                                cell.s.alignment.horizontal = "center";
                                cell.s.font.bold = true;
                                if (R === 0) cell.s.font.sz = 14;
                            } else if (R === 4) {
                                // empty
                            } else if (R === 5) {
                                cell.s.font.bold = true;
                                cell.s.alignment.horizontal = "center";
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                            } else {
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C === 1) cell.s.alignment.horizontal = "left";
                                else cell.s.alignment.horizontal = "center";
                            }

                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'Date') continue;
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal) && C >= 2) {
                                cell.t = 'n';
                                cell.v = numVal;
                                cell.z = '0.00'; 
                            }
                        }
                    }
                }

                var wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, sheet, "Day Product Report");
                
                var safeFileName = 'Date_Wise_Product_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
                XLSX.writeFile(wb, safeFileName);
            }
        }

        function downloadPDFDirect() {
            var element = document.getElementById('report-wrapper');
            if (!element) return;

            var iframe = document.createElement('iframe');
            iframe.style.position = 'absolute';
            iframe.style.top = '-10000px';
            iframe.style.left = '-10000px';
            iframe.style.width = '1200px';
            iframe.style.height = '1000px';
            document.body.appendChild(iframe);
            
            var clonedTable = element.cloneNode(true);
            var hiddenHeader = clonedTable.querySelector('thead tr:first-child td');
            if(hiddenHeader) hiddenHeader.style.display = 'table-cell';

            var tableHTML = clonedTable.outerHTML;
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
                                filename:     'Date_Wise_Product_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_dayproduct', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_dayproduct') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }

    </script>
    @include('admin.includes.date_field')


    <script type="text/javascript" src="{{asset('jq/select2Loads.js')}}"></script>
    <script>
        select2Loads({
            selector: '.dynamic_product',
            url: "/products",
        })

        select2Loads({
            selector: '.supplier',
            url: "/people/suppliers",
        })
    </script>
@stop
