@extends('admin.master')
@section('title', ' - Customer Top Sale Report')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style type="text/css">
        @media screen {
            .hide-on-screen { display: none !important; }
        }
        @media print {
            .d-none { display: block !important; }
            .hide-on-screen { display: table-row !important; }
            .d-print-none { display: none !important; }
        }
    </style>
@endpush

@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="mb-3"><i class="fa fa-line-chart"></i> Customer Top Sale Report</h3>
                    
                    <div class="d-print-none mb-4">
                        <form action="{{ route('reports.customer_top_sales') }}" method="GET">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ $start_date }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="{{ $end_date }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Customer</label>
                                        <select name="customer_id" class="form-control select2">
                                            <option value="">All Customers</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ $customer_id == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }} ({{ $customer->phone }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Supplier</label>
                                        <select name="supplier_id" class="form-control select2">
                                            <option value="">All Suppliers</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ $supplier_id == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Order By</label>
                                        <select name="order_by" class="form-control">
                                            <option value="amount" {{ $order_by == 'amount' ? 'selected' : '' }}>Top by Amount</option>
                                            <option value="quantity" {{ $order_by == 'quantity' ? 'selected' : '' }}>Top by Quantity</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter Report</button>
                                    <button type="button" class="btn btn-success" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div id="report-wrapper">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm" id="top_sale_table">
                                        <thead class="bg-primary text-light">
                                            <tr class="hide-on-screen">
                                                <td colspan="8" class="text-center py-2 bg-white text-dark no-border" style="border: none !important;">
                                                    @include('partials._print_header')
                                                    <h4>Customer Top Sale Report</h4>
                                                    <p>Date: {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') : 'All' }} to {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') : 'All' }}</p>
                                                    <p>Customer: {{ $customer_id ? optional($customers->where('id', $customer_id)->first())->name : 'All Customers' }}</p>
                                                    <p>Supplier: {{ $supplier_id ? optional($suppliers->where('id', $supplier_id)->first())->name : 'All Suppliers' }}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>SL</th>
                                                <th>Customer ID</th>
                                                <th>Customer Name</th>
                                                <th>Phone</th>
                                                <th>Supplier Name</th>
                                                <th class="text-center">Total Invoices</th>
                                                <th class="text-right">Total Quantity</th>
                                                <th class="text-right">Total Amount Spent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $sum_qty = 0;
                                                $sum_amount = 0;
                                            @endphp
                                            @forelse($reports as $key => $report)
                                                @php
                                                    $sum_qty += $report->total_quantity;
                                                    $sum_amount += $report->total_amount;
                                                @endphp
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $report->customer_code ?? 'N/A' }}</td>
                                                    <td>{{ $report->name }}</td>
                                                    <td>{{ $report->phone }}</td>
                                                    <td>{{ $supplier_id ? optional($suppliers->where('id', $supplier_id)->first())->name : 'All Suppliers' }}</td>
                                                    <td class="text-center">{{ $report->total_invoices }}</td>
                                                    <td class="text-right">{{ number_format($report->total_quantity, 2) }}</td>
                                                    <td class="text-right">{{ number_format($report->total_amount, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">No Data Found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot class="bg-light font-weight-bold">
                                            <tr>
                                                <td colspan="6" class="text-right">Grand Total :</td>
                                                <td class="text-right">{{ number_format($sum_qty, 2) }}</td>
                                                <td class="text-right">{{ number_format($sum_amount, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Download Buttons -->
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

                    <div class="d-print-none mt-3">
                        {{ $reports->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select a Customer",
                allowClear: true
            });
        });

        function downloadExcelDirect() {
            var originalTable = document.getElementById('top_sale_table');
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
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Customer Top Sale Report';
                    
                    var filterInfo1 = titleCell.childNodes[5] ? titleCell.childNodes[5].nodeValue : '';
                    var filterInfo2 = titleCell.querySelector('p') ? titleCell.querySelector('p').innerText.replace(/\s+/g, ' ').trim() : '';

                    var filterInfo = (filterInfo1 ? filterInfo1.trim() : '') + (filterInfo2 ? ' ' + filterInfo2 : '');

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 7;

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
                    { wch: 15 },  // Customer ID
                    { wch: 25 },  // Customer Name
                    { wch: 15 },  // Phone
                    { wch: 25 },  // Supplier Name
                    { wch: 15 },  // Total Invoices
                    { wch: 15 },  // Total Quantity
                    { wch: 20 }   // Total Amount Spent
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
                            if (valStr === '' || isNaN(valStr) || valStr === 'SL') continue;
                            
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
                XLSX.utils.book_append_sheet(wb, sheet, "Customer Top Sale");
                
                var safeFileName = 'Customer_Top_Sale_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
                        .hide-on-screen { display: table-row !important; }
                        .bg-primary { background-color: #f8f9fa !important; color: #000 !important; }
                        .no-border { border: none !important; }
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
                                filename:     'Customer_Top_Sale_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' },
                                pagebreak:    { mode: ['avoid-all', 'css'] }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_top_sale', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_top_sale') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
@stop
