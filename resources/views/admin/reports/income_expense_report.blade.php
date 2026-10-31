@extends('admin.master')
@section('title', ' - Income Expense Report')
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
                        <div id="report-wrapper">
                            @include('partials._print_header')

                        <!-- Filter -->
                        <div class="row">
                            <div class="col-md-12 table-responsive">
                                <form >
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="10%">
                                            </td>
                                            <td width="20%" class=" no-print">
                                                <label class="control-label">Start Date</label>
                                                <input type="text" class="form-control dateField" name="from" value="{{ request()->get('from') ?? date('Y-m-d') }}" autocomplete="off">
                                            </td>

                                            <td width="20%" class=" no-print">
                                                <label class="control-label">End Date</label>
                                                <input type="text" class="form-control dateField" name="to" value="{{ request()->get('to') ?? date('Y-m-d') }}" autocomplete="off">
                                            </td>


                                             <td width="30%" class=" no-print text-right">
                                                 <div class="form-group" style="margin-top: 26px;">
                                                     <button class="btn btn-primary"><i class="fa fa-check"></i> Check </button>
                                                     <a href="{{ url('reports/debit_credit') }}" class="btn btn-danger"><i class="fa fa-refresh"></i></a>
                                                 </div>
                                             </td>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                        </div>

                        @isset($reports)
                            <div class="row">
                                <div class="col-md-6 table-responsive">
                                    <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                        <thead>
                                            <tr>
                                                <td colspan="9" class="text-center py-2" style="border: none !important;">
                                                    <h4>Income List</h4>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Sl.</th>
                                                <th>Chart of Account</th>
                                                <th style="padding-right: 10px; text-align: right">Amount</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @php $totalIncome = 0; $i = 2 @endphp
{{--                                            @dd($reports->where('head_type', 0))--}}

                                            <tr>
                                                <td>1</td>
                                                <td>Collection</td>
                                                <td style="padding-right: 10px; text-align: right">{{ number_format($collections ?? 0, 2) }}</td>
                                            </tr>
                                            @foreach ($reports->where('head_type', 0) as $income)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>{{ $income->head_name }}</td>
                                                    <td style="padding-right: 10px; text-align: right">{{ number_format($income->transactions->amount ?? 0, 2) }}</td>
                                                </tr>
                                                @php $totalIncome += $income->transactions->amount ?? 0 @endphp
                                            @endforeach
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <th colspan="2"><strong>Total: </strong></th>
                                                <th style="padding-right: 10px; text-align: right">{{ number_format($totalIncome + $collections, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="col-md-6 table-responsive">
                                    <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                        <thead>
                                            <tr>
                                                <td colspan="9" class="text-center py-2" style="border: none !important;">
                                                    <h4>Expense List</h4>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Sl.</th>
                                                <th>Chart of Account</th>
                                                <th style="padding-right: 10px; text-align: right">Amount</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @php $totalExpense = abs($payments ?? 0); $key = 2; @endphp
                                            <tr>
                                                <td>1</td>
                                                <td>Payment</td>
                                                <td style="padding-right: 10px; text-align: right">{{ number_format(abs($payments) ?? 0, 2) }}</td>
                                            </tr>
                                            @foreach ($reports->where('head_type', 1) as $expense)
                                                <tr>
                                                    <td>{{ $key++ }}</td>
                                                    <td>{{ $expense->head_name }}</td>
                                                    <td style="padding-right: 10px; text-align: right">{{ number_format(abs(optional($expense->transactions)->amount) ?? 0, 2) }}</td>
                                                </tr>
                                                @php $totalExpense += abs(optional($expense->transactions)->amount ?? 0) @endphp
                                            @endforeach
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <th colspan="2"><strong>Total: </strong></th>
                                                <th style="padding-right: 10px; text-align: right">{{ number_format($totalExpense, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Modern Excel & PDF Download Buttons at Bottom Left -->
                            <div class="no-print d-flex align-items-center mt-3" style="gap: 15px; justify-content: flex-start;">
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
                        @endisset
                    </div>
                </div>
            </div>
        </div>
    </main>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="{{ asset('assets/admin/js/plugins/select2.min.js')}}"></script>
    <script type="text/javascript">
        $('.select2').select2();

        function downloadExcelDirect() {
            var tables = document.querySelectorAll('#stock_table');
            if (tables.length < 2) return;
            var incTable = tables[0];
            var expTable = tables[1];

            if (typeof XLSX === 'undefined' || !XLSX.utils.book_append_sheet) {
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js';
                script.onload = performExport;
                document.head.appendChild(script);
            } else {
                performExport();
            }

            function performExport() {
                var titleCell = document.querySelector('#report-wrapper');
                var companyName = titleCell && titleCell.querySelector('h3') ? titleCell.querySelector('h3').innerText : '';
                var address = titleCell && titleCell.querySelectorAll('h5')[0] ? titleCell.querySelectorAll('h5')[0].innerText : '';
                var contact = titleCell && titleCell.querySelectorAll('h5')[1] ? titleCell.querySelectorAll('h5')[1].innerText : '';
                var reportTitle = 'Income & Expense Report';
                var dateInfo = titleCell && titleCell.querySelector('p') ? titleCell.querySelector('p').innerText.replace(/\s+/g, ' ').trim() : '';

                // Get all rows of income and expense
                var incRows = Array.from(incTable.querySelectorAll('tr'));
                var expRows = Array.from(expTable.querySelectorAll('tr'));
                var maxRows = Math.max(incRows.length, expRows.length);

                var virtualTable = document.createElement('table');
                var tbody = document.createElement('tbody');
                virtualTable.appendChild(tbody);

                var newRowsHtml = `
                    <tr><td colspan="7" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                    <tr><td colspan="7" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                    <tr><td colspan="7" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                    <tr><td colspan="7" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                    ${dateInfo ? `<tr><td colspan="7" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${dateInfo}</td></tr>` : ''}
                    <tr><td colspan="7" style="border: none; height: 10px;"></td></tr>
                `;
                tbody.innerHTML = newRowsHtml;

                for (var i = 0; i < maxRows; i++) {
                    var tr = document.createElement('tr');
                    
                    // Income table cells
                    if (i < incRows.length) {
                        var cells = Array.from(incRows[i].cells);
                        cells.forEach(function(c) {
                            var newCell = c.cloneNode(true);
                            if (newCell.getAttribute('colspan') === '9') {
                                newCell.setAttribute('colspan', '3');
                            }
                            tr.appendChild(newCell);
                        });
                    } else {
                        tr.innerHTML += '<td></td><td></td><td></td>';
                    }

                    // Separator column (Col D / Index 3)
                    var sepCell = document.createElement('td');
                    sepCell.style.width = '30px';
                    sepCell.style.border = 'none';
                    tr.appendChild(sepCell);

                    // Expense table cells
                    if (i < expRows.length) {
                        var cells = Array.from(expRows[i].cells);
                        cells.forEach(function(c) {
                            var newCell = c.cloneNode(true);
                            if (newCell.getAttribute('colspan') === '9') {
                                newCell.setAttribute('colspan', '3');
                            }
                            tr.appendChild(newCell);
                        });
                    } else {
                        tr.innerHTML += '<td></td><td></td><td></td>';
                    }

                    tbody.appendChild(tr);
                }

                var sheet = XLSX.utils.table_to_sheet(virtualTable, { raw: false });

                var range = XLSX.utils.decode_range(sheet['ref'] || sheet['!ref']);
                var colWidths = [
                    { wch: 6 },   // Income Sl
                    { wch: 25 },  // Income Account
                    { wch: 15 },  // Income Amount
                    { wch: 4 },   // Separator
                    { wch: 6 },   // Expense Sl
                    { wch: 25 },  // Expense Account
                    { wch: 15 }   // Expense Amount
                ];
                sheet['!cols'] = colWidths;

                var headerRowCount = 5;
                if(dateInfo) headerRowCount++;

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
                                // empty spacer row
                            } else if (C === 3) {
                                // Separator column
                                cell.s.border = {};
                            } else {
                                // Table data styling
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                
                                // Determine row type (Header vs Body vs Footer)
                                // Let's check table headers
                                var isTableTitleRow = (R === headerRowCount || R === headerRowCount + incRows.length);
                                var isHeaderRow = (R === headerRowCount + 1 || R === headerRowCount + incRows.length + 1);

                                var valStr = (cell.v + '').replace(/,/g, '').trim();

                                if (valStr.includes('Income List') || valStr.includes('Expense List') || valStr === 'Sl.' || valStr === 'Chart of Account' || valStr === 'Amount' || valStr.includes('Total:')) {
                                    cell.s.font.bold = true;
                                    cell.s.alignment.horizontal = "center";
                                    if (valStr.includes('Total:')) {
                                        cell.s.alignment.horizontal = "right";
                                    }
                                } else {
                                    if (C === 0 || C === 4) cell.s.alignment.horizontal = "center";
                                    else if (C === 2 || C === 6) cell.s.alignment.horizontal = "right";
                                    else cell.s.alignment.horizontal = "left";
                                }

                                if (valStr !== '' && !isNaN(valStr) && valStr !== 'Sl.' && !valStr.includes('Total:')) {
                                    var numVal = parseFloat(valStr);
                                    if (!isNaN(numVal)) {
                                        if (C === 2 || C === 6) {
                                            cell.t = 'n';
                                            cell.v = numVal;
                                            cell.z = '#,##0.00';
                                        } else if (C === 0 || C === 4) {
                                            cell.t = 'n';
                                            cell.v = numVal;
                                            cell.z = '0';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                var wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, sheet, "Income & Expense");
                
                var safeFileName = 'Income_Expense_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
                    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
                    <style>
                        body { font-family: Arial, sans-serif; font-size: 11px; background: #fff; margin: 0; padding: 20px; color: #000; }
                        table { width: 100%; border-collapse: collapse; background: #fff; margin-bottom: 20px; }
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
                            var dPrints = document.querySelectorAll('.d-print');
                            dPrints.forEach(function(el) {
                                el.style.display = 'block';
                            });
                            
                            var opt = {
                                margin:       [0.5, 0.5, 0.5, 0.5],
                                filename:     'Income_Expense_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_ie', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_ie') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
    @include('admin.includes.date_field')
@endsection
