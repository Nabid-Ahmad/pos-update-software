
@extends('admin.master')
@section('title', ' - Product Wise Sales Report')

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

@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">

                        <input class="is-add-product-cost" type="hidden" value="{{ request('update_product_cost') }}">

                        <!-- Filter -->
                        <div class="row d-print-none filter-area">
                            <div class="col-md-11 mx-auto">
                                <div class="table-responsive">
                                    <form action="{{ route('product.wise.sales.report') }}" method="get" id="reportForm">
                                        <input type="hidden" name="update_product_cost" value="">

                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td width="20%">
                                                    <label>Customer</label>
                                                    <select name="fk_customer_id" class="form-control select2">
                                                        <option value="">All</option>
                                                        @foreach ($customers as $key => $customer)
                                                            <option value="{{ $customer->id }}" {{ $customer->id == request()->fk_customer_id ? 'selected':'' }}>{{ $customer->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                
                                                <td width="20%">
                                                    <label>Product</label>
                                                    <select name="product_id" class="form-control select2">
                                                        <option value="">All</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}" {{ request()->product_id == $product->id ? 'selected':'' }}>{{ $product->product_name }}</option>
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


                                                <td width="30%" class="no-print text-right">
                                                    <div class="form-group" style="margin-top: 26px;">
                                                        <button class="btn btn-primary"><i class="fa fa-check"></i> Check </button>
                                                        <a href="{{ route('product.wise.sales.report') }}" class="btn btn-danger"><i class="fa fa-refresh"></i></a>

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

                        <!-- Sale List -->
                        <div class="row">
                            <div class="col-sm-11 mx-auto">
                                <div id="report-wrapper">
                                <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                    <thead>
                                        <tr>
                                            <td colspan="9" class="text-center py-2" style="border: none !important;">
                                                @include('partials._print_header')
                                                <h4>Product Wise Sales Report</h4>
                                                <p class="mb-1">
                                                    <strong>Customer:</strong> {{ request('fk_customer_id') ? optional($customers->where('id', request('fk_customer_id'))->first())->name : 'All' }} | 
                                                    <strong>Product:</strong> {{ request('product_id') ? optional($products->where('id', request('product_id'))->first())->product_name : 'All' }}
                                                </p>

                                                @if (request('start_date'))
                                                    <p>
                                                        Showing Sales From <b>{{ request('start_date') }}</b>
                                                        to <b>{{ request('end_date') }}</b>
                                                    </p>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="5%">Sl.</th>
                                            <th width="15%">Date</th>
                                            <th width="15%" style="text-align: center">Invoice ID </th>
                                            <th width="15%">Customer </th>
                                            <th width="15%">Product Name</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Discount</th>
                                            <th class="text-right">Total Price</th>
                                        </tr>
                                    </thead>

                                                                    <tbody>
                                    @php
                                        $totalQty = 0;
                                        $totalPrice = 0;
                                    @endphp
                                    @foreach ($salesDetails as $key => $detail)
                                        @php
                                            $totalQty += $detail->quantity;
                                            $totalPrice += $detail->product_sub_total;
                                        @endphp
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($detail->sale->sale_date)->format('d-m-Y') }}</td>
                                            <td><a href="{{ route('sales.show', $detail->fk_sales_id) }}" target="_blank">{{ $detail->sale->invoiceId }}</a></td>
                                            <td>{{ $detail->sale->sale_customer->name ?? '' }}</td>
                                            <td>{{ $detail->product->product_name ?? '' }}</td>
                                            <td class="text-center">{{ $detail->quantity }}</td>
                                            <td class="text-right">{{ number_format($detail->unit_price, 2) }}</td>
                                            <td class="text-right">{{ number_format($detail->product_discount, 2) }}%</td>
                                            <td class="text-right">{{ number_format($detail->product_sub_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-right"><strong>Total:</strong></td>
                                        <td class="text-center"><strong>{{ $totalQty }}</strong></td>
                                        <td colspan="2"></td>
                                        <td class="text-right"><strong>{{ number_format($totalPrice, 2) }}</strong></td>
                                    </tr>
                                </tfoot>
                                </table>
                                </div>

                                <div class="row d-print-none mt-3">
                                    <div class="col-md-6 text-left" style="display: flex; gap: 12px; align-items: center;">
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
        })

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
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Sales Report';
                    
                    var filterInfo1 = titleCell.childNodes[5] ? titleCell.childNodes[5].nodeValue : '';
                    var filterInfo2 = titleCell.querySelector('p') ? titleCell.querySelector('p').innerText.replace(/\s+/g, ' ').trim() : '';

                    var filterInfo = (filterInfo1 ? filterInfo1.trim() : '') + (filterInfo2 ? ' ' + filterInfo2 : '');

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 8;

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
                    { wch: 6 },   // Sl
                    { wch: 15 },  // Date
                    { wch: 15 },  // Invoice ID
                    { wch: 25 },  // Customer
                    { wch: 25 },  // Product Name
                    { wch: 10 },  // Quantity
                    { wch: 15 },  // Unit Price
                    { wch: 15 },  // Discount
                    { wch: 15 }   // Total Price
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
                                if(C >= 4) cell.s.alignment.horizontal = "right";
                                if(C === 0 && cell.v.toString().includes('Total')) cell.s.alignment.horizontal = "left";
                            } else {
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C === 0 || C === 1) cell.s.alignment.horizontal = "left";
                                else if (C >= 4) cell.s.alignment.horizontal = "right";
                                else cell.s.alignment.horizontal = "center";
                            }

                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'Sl.') continue;
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal) && C >= 4) {
                                cell.t = 'n';
                                cell.v = numVal;
                                cell.z = '#,##0.00'; 
                            } else if (!isNaN(numVal) && C === 0) {
                                cell.t = 'n';
                                cell.v = numVal;
                                cell.z = '0';
                            }
                        }
                    }
                }

                var wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, sheet, "Product Wise Sales Report");
                
                var safeFileName = 'Product_Wise_Sales_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
                        tr { page-break-inside: avoid; break-inside: avoid; }
                        th, td { border: 1px solid #dee2e6 !important; padding: 6px 8px; vertical-align: middle; color: #000; }
                        th { background-color: #f8f9fa; font-weight: bold; }
                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        .text-left { text-align: left; }
                        h3, h4, h5, p { text-align: center; margin: 5px 0; color: #000; }
                        a { text-decoration: none; color: #000; }
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
                                filename:     'Product_Wise_Sales_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' },
                                pagebreak:    { mode: ['avoid-all', 'css'] }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_sales', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_sales') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
    @include('admin.includes.date_field')
@endsection
