@extends('admin.master')
@section('title', ' - Customer Report')

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
        .debit-cell, .credit-cell, .balance-cell, th.text-right, td.text-right {
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

                        <!-- Filter -->
                        <div class="row">
                            <div class="col-md-12 no-print">
                                <form action="{{ route('customers.report') }}" method="get">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="5%"></td>
                                            <td class="no-print" width="20%">
                                                <label class="control-label">Customer</label>
                                                <select name="customer_id" class="form-control select2">
                                                    <option value="">All</option>
                                                    @foreach($customers as $id => $name)
                                                        <option value="{{ $id }}" {{ $id == request('customer_id') ? 'selected':'' }}>{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td class="no-print" width="20%">
                                                <label class="control-label">Supplier</label>
                                                <select name="supplier_id" class="form-control select2">
                                                    <option value="">All</option>
                                                    @foreach($suppliers as $id => $name)
                                                        <option value="{{ $id }}" {{ $id == request('supplier_id') ? 'selected':'' }}>{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td class="no-print" width="8%">
                                                <label class="control-label">Start Date</label>
                                                <input type="text" class="form-control dateField" name="from" value="{{ request()->get('from') ?? date('Y-m-d') }}" autocomplete="off">
                                            </td>

                                            <td class="no-print" width="8%">
                                                <label class="control-label">End Date</label>
                                                <input type="text" class="form-control dateField" name="to" value="{{ request()->get('to') ?? date('Y-m-d') }}" autocomplete="off">
                                            </td>


                                            <td class="no-print" width="20%">
                                                <div class="form-group" style="margin-top: 26px;">
                                                    <button class="btn btn-primary"><i class="fa fa-check"></i> Check </button>
                                                    <a href="{{ url('reports/customer_report') }}"
                                                       class="btn btn-danger"><i class="fa fa-refresh"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                        </div>


                        <!-- Customer report data -->
                        <div class="table-responsive">
                            <div class="col-sm-11 mx-auto">
                                <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                    <thead>
                                        <tr>
                                            <td colspan="7" class="text-center py-2" style="border: none !important;">
                                                @include('partials._print_header')
                                                <h4>Customer Report</h4>
                                                @if (request()->filled('customer_id'))
                                                    <p>
                                                        Customer:
                                                        <span style="font-weight:bold">{{ $customer->name }}
                                                        <br></span>
                                                        {{ $customer->phone }}, {{ $customer->email }}
                                                    </p>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="5%">Sl.</th>
                                            <th width="12%">Date</th>
                                            <th width="12%">Invoice Id</th>
                                            <th>Description</th>
                                            <th width="10%" class="text-right">Debit/Sales</th>
                                            <th width="10%" class="text-right">Credit/Receive</th>
                                            <th class="text-right">Balance</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @isset($transactions)
                                            <tr>
                                                <th colspan="6" class="pl-2">Opening Balance</th>
                                                <th class="text-right balance-cell">{{ number_format($openingBalance, 2) }}</th>
                                            </tr>


                                            @php $sl = 1 @endphp
                                            @foreach($transactions as $key => $transaction)
                                                <tr>
                                                    <td>{{ $sl++ }}</td>
                                                    <td> {{ $transaction['date'] }} </td>
                                                    <td>
                                                        @if($transaction['type'] == 'Sale')
                                                            <a href="{{ route('sales.show', $transaction['source_id']) }}?print_type=invoice" target="_blank">{{ $transaction['invoice_id'] }}</a>
                                                        @elseif($transaction['type'] == 'Collection' || $transaction['type'] == 'Opening Balance' || $transaction['type'] == 'Due Collection' || str_contains($transaction['invoice_id'], 'CDC'))
                                                            <a href="{{ route('customer-collections.show', $transaction['source_id']) }}" target="_blank">{{ $transaction['invoice_id'] }}</a>
                                                        @else
                                                            <a href="javascript:void(0)" >{{ $transaction['invoice_id'] }}</a>
                                                        @endif
                                                    </td>
                                                    <td> {{ $transaction['type'] }} {{ ($transaction['discount'] ?? 0) > 0 ? ( ' (' . ($transaction['discount'] ?? 0) . ')') : '' }} </td>
                                                    <td class="text-right debit-cell">{{ number_format(abs($transaction['debit']), 2) }}</td>
                                                    <td class="text-right credit-cell">{{ number_format(abs($transaction['credit']), 2) }}</td>
                                                    <td  class="text-right balance-cell">
                                                        {{ number_format($openingBalance += ($transaction['debit'] - $transaction['credit'] - ($transaction['discount'] ?? 0)), 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endisset
                                    </tbody>
                                </table>
                                <p class="text-muted">
                                    *Positive balance means that amount is showing as customer's due. (
                                    ব্যালেন্স ধনাত্মক হলে তা উক্ত  কাস্টমার  এর  বাকী  টাকা হিসেবে বিবেচিত
                                    হবে এবং ঋণাত্মক হলে জমা)
                                </p>

                                <!-- Modern Excel & PDF Download Buttons at Bottom Left -->
                                <div class="no-print d-flex align-items-center mt-3 mb-2" style="gap: 15px; justify-content: flex-start;">
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
                                    <a href="javascript:void(0)" onclick="downloadExcelDirect()" class="download-icon-btn" title="Download Excel">
                                        @include('admin.reports.includes.excel_icon')
                                    </a>

                                    <!-- PDF Icon -->
                                    <a href="javascript:void(0)" onclick="downloadPDFDirect()" class="download-icon-btn" title="Download PDF">
                                        @include('admin.reports.includes.pdf_icon')
                                    </a>
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


    @include('admin.includes.date_field')

    @include('admin.includes.delete_confirm')

    @section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadExcelDirect() {
            var originalTable = document.getElementById('stock_table');
            if (!originalTable) return;
            
            // Dynamically load xlsx-js-style instead of standard sheetjs
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

                // Remove all elements with the 'd-print-none' class
                var dPrintNoneElements = clonedTable.querySelectorAll('.d-print-none');
                dPrintNoneElements.forEach(function(el) {
                    el.parentNode.removeChild(el);
                });

                // Adjust the header colspan and split the nested header into clean separate rows
                var titleRow = clonedTable.querySelector('thead tr');
                if (titleRow && titleRow.querySelector('td[colspan], th[colspan]')) {
                    var titleCell = titleRow.querySelector('td[colspan], th[colspan]');
                    
                    var companyName = titleCell.querySelector('h3') ? titleCell.querySelector('h3').innerText : '';
                    var address = titleCell.querySelectorAll('h5')[0] ? titleCell.querySelectorAll('h5')[0].innerText : '';
                    var contact = titleCell.querySelectorAll('h5')[1] ? titleCell.querySelectorAll('h5')[1].innerText : '';
                    var customerInfo = titleCell.querySelector('p') ? titleCell.querySelector('p').innerText.replace(/\s+/g, ' ').trim() : '';
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Customer Report';

                    // Dynamically calculate exact number of columns to merge so headers center perfectly over the data
                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 7;

                    var newRowsHtml = `
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                        ${customerInfo ? `<tr><td colspan="${colspanVal}" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${customerInfo}</td></tr>` : ''}
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                        <tr><td colspan="${colspanVal}" style="border: none; height: 10px;"></td></tr>
                    `;
                    
                    var tempDiv = document.createElement('tbody');
                    tempDiv.innerHTML = newRowsHtml;
                    var parent = titleRow.parentNode;
                    
                    while (tempDiv.firstChild) {
                        parent.insertBefore(tempDiv.firstChild, titleRow);
                    }
                    parent.removeChild(titleRow);
                }

                // Convert table to SheetJS sheet
                var sheet = XLSX.utils.table_to_sheet(clonedTable, { raw: false });

                // Define column widths dynamically
                var range = XLSX.utils.decode_range(sheet['!ref']);
                var numCols = range.e.c - range.s.c + 1;
                var colWidths = [
                    { wch: 8 },   // Sl.
                    { wch: 15 },  // Date
                    { wch: 15 },  // Invoice Id
                    { wch: 30 },  // Description
                    { wch: 18 },  // Debit/Sales
                    { wch: 18 },  // Credit/Receive
                    { wch: 18 }   // Balance
                ];
                sheet['!cols'] = colWidths;

                // Loop through all data cells in the sheet and format numbers & styles correctly
                var headerRowCount = customerInfo ? 6 : 5; // Rows before the actual table header

                for (var R = range.s.r; R <= range.e.r; ++R) {
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        var cell_ref = XLSX.utils.encode_cell({c: C, r: R});
                        var cell = sheet[cell_ref];
                        if (cell && cell.v !== undefined) {
                            
                            // Initialize styles
                            cell.s = {
                                font: { name: 'Arial', sz: 11 },
                                alignment: { vertical: "center" }
                            };

                            // Apply styles based on row type
                            if (R < headerRowCount - 1) {
                                // Top Company Headers
                                cell.s.alignment.horizontal = "center";
                                cell.s.font.bold = true;
                                if (R === 0) cell.s.font.sz = 14; // Company Name larger
                            } 
                            else if (R === headerRowCount - 1) {
                                // Empty row before table
                            }
                            else if (R === headerRowCount) {
                                // Table Headers (Sl, Date, etc)
                                cell.s.font.bold = true;
                                cell.s.alignment.horizontal = "center";
                                cell.s.border = {
                                    top: {style: "thin"}, bottom: {style: "thin"},
                                    left: {style: "thin"}, right: {style: "thin"}
                                };
                            }
                            else {
                                // Table Data Rows
                                cell.s.border = {
                                    top: {style: "thin"}, bottom: {style: "thin"},
                                    left: {style: "thin"}, right: {style: "thin"}
                                };
                                
                                if (C === 0) {
                                    cell.s.alignment.horizontal = "left"; // Sl to left
                                } else if (C >= 4 && C <= 6) {
                                    cell.s.alignment.horizontal = "right"; // Amounts to right
                                } else {
                                    cell.s.alignment.horizontal = "center";
                                }
                            }

                            // Value parsing for numbers
                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'Sl.' || valStr === 'Grand Total:' || valStr === 'Total this page:' || valStr === 'Opening Balance') {
                                continue;
                            }
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal)) {
                                cell.t = 'n';
                                cell.v = numVal;

                                if (C === 0) cell.z = '0'; // Sl.
                                if (C >= 4 && C <= 6) cell.z = '#,##0.00'; // Debit, Credit, Balance
                            }
                        }
                    }
                }

                // Create workbook and write file
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
            // Move off-screen instead of using display: none or visibility: hidden (which breaks html2canvas)
            iframe.style.position = 'absolute';
            iframe.style.top = '-10000px';
            iframe.style.left = '-10000px';
            iframe.style.width = '1000px';
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
                            // Strip inline styles that cause blank rendering or missing borders
                            var table = document.querySelector('table');
                            if(table) table.removeAttribute('style');
                            var cells = document.querySelectorAll('td, th');
                            cells.forEach(function(c) { c.removeAttribute('style'); });

                            var opt = {
                                margin:       [0.5, 0.5, 0.5, 0.5],
                                filename:     'Customer_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' },
                                pagebreak:    { mode: ['avoid-all', 'css'] }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_customer', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_customer') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
@endsection
@endsection
