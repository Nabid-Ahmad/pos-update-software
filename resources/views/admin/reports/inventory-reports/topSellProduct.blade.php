
@extends('admin.master')
@section('title', ' - Top Sales Report')

@push('style')
    <style type="text/css">
        @media print {
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
    </style>
@endpush
@php
    $count = 0;
@endphp
@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <!-- Filter -->
                        <div class="row d-print-none filter-area">
                            <div class="col-md-11 mx-auto">
                                <div class="table-responsive">
                                    <form action="" method="get">
                                        <input type="hidden" name="update_product_cost" value="">

                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td width="20%">
                                                    <label>Products</label>
                                                    {{-- @dd($selectproducts) --}}
                                                    <select name="fk_product_id" class="form-control select2">
                                                        <option value="">All</option>
                                                        @foreach ($selectproducts as $key => $selectproduct)
                                                            <option value="{{ $selectproduct->id }}" {{ $selectproduct->id == request()->fk_product_id ? 'selected':'' }}>{{ $selectproduct->product_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="15%">
                                                    <label>Customers</label>
                                                    <select name="fk_customer_id" class="form-control select2">
                                                        <option value="">All Customers</option>
                                                        @foreach ($customers as $customer)
                                                            <option value="{{ $customer->id }}" {{ request()->fk_customer_id == $customer->id ? 'selected':'' }}>{{ $customer->name }} ({{ $customer->phone }})</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="15%">
                                                    <label>Suppliers</label>
                                                    <select name="fk_supplier_id" class="form-control select2">
                                                        <option value="">All Suppliers</option>
                                                        @foreach ($suppliers as $supplier)
                                                            <option value="{{ $supplier->id }}" {{ request()->fk_supplier_id == $supplier->id ? 'selected':'' }}>{{ $supplier->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="15%" class="no-print">
                                                    <label class="control-label">Start Date</label>
                                                    <input type="text" class="form-control dateField" name="start_date" value="{{ request()->get('start_date') ?? date('Y-m-d') }}" autocomplete="off">
                                                </td>

                                                <td width="15%" class="no-print">
                                                    <label class="control-label">End Date</label>
                                                    <input type="text" class="form-control dateField" name="end_date" value="{{ request()->get('end_date') ?? date('Y-m-d') }}" autocomplete="off">
                                                </td>


                                                <td width="25%" class="no-print text-right">
                                                    <div class="form-group" style="margin-top: 26px;">
                                                        <button class="btn btn-primary"><i class="fa fa-check"></i> Check </button>
                                                        <a href="{{ url('reports/sales') }}" class="btn btn-danger"><i class="fa fa-refresh"></i></a>
                                                        <button type="button" class="btn btn-success" onclick="window.print()">
                                                            <i class="fa fa-print"></i> Print
                                                        </button>
                                                        @if (auth()->user()->id == 1)
                                                            <button type="button" class="btn btn-warning" onclick="addProductCost(1)">
                                                                <i class="fa fa-gear"></i> Add Product Cost
                                                            </button>
                                                        @endif

                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <form action="" method="get" style="width: 100% !important;" class="add-product-cost-area">
                            <div class="row d-print-none ">
                                <input type="hidden" name="update_product_cost" value="update">
                                <div class="col-sm-12 text-center">
                                    <span class="text-success">{{ $count }} updated</span>
                                </div>

                                <div class="col-sm-4">
                                    <p>From:</p>
                                    <input name="from" type="number" class="form-control" value="{{ request('from') }}">
                                </div>
                                <div class="col-sm-4">
                                    <p>Take:</p>
                                    <input name="count" type="number" class="form-control" value="{{ request('count') }}">
                                </div>

                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                <div class="col-sm-4">
                                    <p>Action</p>
                                    <button type="submit" class="btn btn-primary">Update Product Cost</button>
                                    <button type="button" onclick="addProductCost(0)" class="btn btn-danger">Cancel</button>
                                </div>
                        </div>
                    </form>

                        <!-- Sale List -->
                        <div class="row">
                            <div class="col-sm-11 mx-auto">
                                <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                    <thead>
                                        <tr>
                                            <td colspan="9" class="text-center py-2" style="border: none !important;">
                                                @include('partials._print_header')
                                                <h4>Top Sales Report</h4>
                                                For {{ request('fk_product_id') ? optional($selectproducts->where('id', request('fk_product_id'))->first())->name : ' All Products' }}

                                                @if (request('start_date'))
                                                    <p>
                                                        Showing Sales From <b>{{ request('start_date') }}</b>
                                                        to <b>{{ request('end_date') }}</b>
                                                    </p>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="10%">Sl.</th>
                                            <th width="20%" class="text-left">Product Name</th>
                                            <th width="10%" class="text-center">Product Code</th>
                                            <th width="20%" class="text-center">Customer Name</th>
                                            <th width="20%" class="text-center">Supplier Name</th>
                                            <th class="text-right" width="20%">Total Sold Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalQuantity = 0;
                                        @endphp
                                        @foreach($topSellProducts as $key => $item)
                                            <tr>
                                                <td>{{ $key + 1}}</td>
                                                <td class="text-left">{{ optional($item->product)->product_name ?? 'Unknown Product' }}</td>
                                                <td class="text-center">{{ optional($item->product)->product_code ?? '-' }}</td>
                                                @if (request('fk_product_id'))
                                                    <td class="text-center">{{ $item->customer_name ?? 'Unknown Customer' }}</td>
                                                @else
                                                    <td class="text-center">{{ request('fk_customer_id') ? optional($customers->where('id', request('fk_customer_id'))->first())->name : 'All Customers' }}</td>
                                                @endif
                                                <td class="text-center">{{ optional(optional($item->product)->supplier)->name ?? 'All Suppliers' }}</td>
                                                @php
                                                    $totalQuantity += $item->total_quantity;
                                                @endphp
                                                <td class="text-right"> {{ number_format($item->total_quantity, 2) }} </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <th colspan="5" class="text-right">Grand Total:</th>
                                            <td class="text-right font-weight-bold">{{ number_format($totalQuantity, 2) }}</td>
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
        </div>
    </main>


    <script src="{{ asset('assets/admin/js/jquery-3.2.1.min.js') }}"></script>
    <!-- Data table plugin-->
    <script type="text/javascript" src="{{ asset('assets/admin/js/plugins/jquery.dataTables.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('assets/admin/js/plugins/dataTables.bootstrap.min.js')}}"></script>

    <script src="{{ asset('assets/admin/js/plugins/select2.min.js')}}"></script>

    <script type="text/javascript">
        $('.filter-area').hide()
        $('.add-product-cost-area').hide()
        function addProductCost(status)
        {
            if (status == 1) {
                $('.filter-area').hide()
                $('.add-product-cost-area').show()
            } else {
                $('.filter-area').show()
                $('.add-product-cost-area').hide()
            }
        }
        $(document).ready(function () {
            if($('.is-add-product-cost').val() == 'update') {
                addProductCost(1)
            } else {
                addProductCost(0)
            }
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

                var titleRow = clonedTable.querySelector('thead tr');
                if (titleRow && titleRow.querySelector('td[colspan], th[colspan]')) {
                    var titleCell = titleRow.querySelector('td[colspan], th[colspan]');
                    
                    var companyName = titleCell.querySelector('h3') ? titleCell.querySelector('h3').innerText : '';
                    var address = titleCell.querySelectorAll('h5')[0] ? titleCell.querySelectorAll('h5')[0].innerText : '';
                    var contact = titleCell.querySelectorAll('h5')[1] ? titleCell.querySelectorAll('h5')[1].innerText : '';
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Top Sales Report';
                    
                    // The text after h4 usually contains "For All Products" and the dates
                    var filterInfo1 = titleCell.childNodes[5] ? titleCell.childNodes[5].nodeValue : '';
                    var filterInfo2 = titleCell.querySelector('p') ? titleCell.querySelector('p').innerText.replace(/\s+/g, ' ').trim() : '';

                    var filterInfo = (filterInfo1 ? filterInfo1.trim() : '') + (filterInfo2 ? ' ' + filterInfo2 : '');

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 5;

                    var newRowsHtml = `
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                        ${filterInfo ? `<tr><td colspan="${colspanVal}" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${filterInfo}</td></tr>` : ''}
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
                    { wch: 8 },   // Sl.
                    { wch: 30 },  // Product Name
                    { wch: 20 },  // Product Code
                    { wch: 25 },  // Customer Name
                    { wch: 25 },  // Supplier Name
                    { wch: 20 }   // Total Sold Quantity
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
                                if(C === 3) cell.s.alignment.horizontal = "right";
                            } else {
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C === 0) cell.s.alignment.horizontal = "left";
                                else if (C === 4) cell.s.alignment.horizontal = "right";
                                else if (C === 1) cell.s.alignment.horizontal = "left";
                                else cell.s.alignment.horizontal = "center";
                            }

                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'Sl.' || valStr === 'Grand Total:') continue;
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal)) {
                                cell.t = 'n';
                                cell.v = numVal;
                                if (C === 0) cell.z = '0'; 
                                if (C === 4) cell.z = '#,##0.00'; 
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
                                filename:     'Top_Sales_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_topsales', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_topsales') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
    @include('admin.includes.date_field')
@endsection
