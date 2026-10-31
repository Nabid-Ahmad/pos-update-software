@extends('admin.master')

@push('style')
    <style type="text/css">
        @media print {
            .no-print {
                display: none !important;
            }
        }
        .pl-table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }
        .pl-table th, .pl-table td {
            padding: 0.75rem;
            vertical-align: top;
            border-bottom: 1px solid #dee2e6;
        }
        .pl-heading {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .pl-sub-item {
            padding-left: 2rem !important;
        }
        .pl-total {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .pl-final-total {
            font-size: 1.3rem;
            font-weight: bold;
            text-align: right;
            padding: 15px !important;
        }
    </style>
@endpush

@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-header no-print">
                        <h3 class="text-center">Profit & Loss Statement</h3>
                        <hr>
                    </div>
                    <div class="tile-body">
                        <!-- Filters -->
                        <div class="row no-print mb-4">
                            <div class="col-md-12">
                                <form action="" method="GET" class="form-inline justify-content-center">
                                    <div class="form-group mx-sm-3 mb-2">
                                        <label for="from" class="sr-only">Start Date</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">From</span></div>
                                            <input type="text" class="form-control dateField" name="from" id="from" value="{{ $from }}" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group mx-sm-3 mb-2">
                                        <label for="to" class="sr-only">End Date</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">To</span></div>
                                            <input type="text" class="form-control dateField" name="to" id="to" value="{{ $to }}" autocomplete="off">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mb-2 mx-1"><i class="fa fa-filter"></i> Filter</button>
                                    <a href="{{ url('reports/profit_loss_report') }}" class="btn btn-danger mb-2 mx-1"><i class="fa fa-refresh"></i> Reset</a>
                                    <button type="button" class="btn btn-success mb-2 mx-1" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
                                </form>
                            </div>
                        </div>

                        <div id="report-wrapper">
                            <!-- Statement Header for Print -->
                            <div class="row mb-4 text-center">
                                <div class="col-12">
                                    @include('partials._print_header')
                                    <h4>Income Statement (Profit & Loss)</h4>
                                    <p>For the period: <strong>{{ date('d M, Y', strtotime($from)) }}</strong> to <strong>{{ date('d M, Y', strtotime($to)) }}</strong></p>
                                </div>
                            </div>

                            <!-- Statement Table -->
                            <div class="row">
                                <div class="col-md-10 offset-md-1 table-responsive">
                                    <table class="pl-table" id="stock_table">
                                    <tbody>
                                        <!-- Sales Revenue -->
                                        <tr class="pl-heading">
                                            <td colspan="2">A. Sales Revenue</td>
                                        </tr>
                                        <tr>
                                            <td class="pl-sub-item">Gross Sales</td>
                                            <td class="text-right">{{ number_format($grossSales, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="pl-sub-item">Less: Total Discounts</td>
                                            <td class="text-right text-danger">- {{ number_format($totalDiscounts, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="pl-sub-item">Less: Sales Returns</td>
                                            <td class="text-right text-danger">- {{ number_format($salesReturnAmount, 2) }}</td>
                                        </tr>
                                        <tr class="pl-total">
                                            <td>Net Sales Revenue</td>
                                            <td class="text-right">{{ number_format($netSalesRevenue, 2) }}</td>
                                        </tr>

                                        <tr><td colspan="2" style="border: none; padding: 10px;"></td></tr>

                                        <!-- Cost of Goods Sold -->
                                        <tr class="pl-heading">
                                            <td colspan="2">B. Cost of Goods Sold (COGS)</td>
                                        </tr>
                                        <tr>
                                            <td class="pl-sub-item">Product Purchase Cost (of sold items)</td>
                                            <td class="text-right">{{ number_format($totalCOGS, 2) }}</td>
                                        </tr>
                                        <tr class="pl-total">
                                            <td>Total Cost of Goods Sold</td>
                                            <td class="text-right">{{ number_format($totalCOGS, 2) }}</td>
                                        </tr>

                                        <tr><td colspan="2" style="border: none; padding: 10px;"></td></tr>

                                        <!-- Gross Profit -->
                                        <tr class="pl-heading text-primary">
                                            <td>C. GROSS PROFIT (A - B)</td>
                                            <td class="text-right">{{ number_format($grossProfit, 2) }}</td>
                                        </tr>

                                        <tr><td colspan="2" style="border: none; padding: 10px;"></td></tr>

                                        <!-- Operating Expenses -->
                                        <tr class="pl-heading">
                                            <td colspan="2">D. Operating Expenses</td>
                                        </tr>
                                        @forelse($expensesDetails as $expense)
                                        <tr>
                                            <td class="pl-sub-item">{{ $expense->head_name }}</td>
                                            <td class="text-right text-danger">- {{ number_format($expense->total_amount, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td class="pl-sub-item">No Expenses Recorded</td>
                                            <td class="text-right text-danger">- 0.00</td>
                                        </tr>
                                        @endforelse
                                        <tr class="pl-total">
                                            <td>Total Operating Expenses</td>
                                            <td class="text-right">{{ number_format($totalOperatingExpenses, 2) }}</td>
                                        </tr>

                                        <!-- Other Income -->
                                        <tr class="pl-heading text-success">
                                            <td colspan="2">E. Other Income</td>
                                        </tr>
                                        @forelse($incomeDetails as $income)
                                        <tr>
                                            <td class="pl-sub-item">{{ $income->head_name }}</td>
                                            <td class="text-right text-success">+ {{ number_format($income->total_amount, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td class="pl-sub-item">No Other Income Recorded</td>
                                            <td class="text-right text-success">+ 0.00</td>
                                        </tr>
                                        @endforelse
                                        <tr class="pl-total">
                                            <td>Total Other Income</td>
                                            <td class="text-right text-success">{{ number_format($totalOtherIncome, 2) }}</td>
                                        </tr>

                                        <tr><td colspan="2" style="border: none; padding: 10px;"></td></tr>

                                        <!-- Net Profit -->
                                        <tr>
                                            <td colspan="2" class="pl-final-total text-{{ $netProfit >= 0 ? 'success' : 'danger' }}" style="border-top: 3px solid #dee2e6; border-bottom: 3px double #dee2e6;">
                                                F. NET PROFIT OR LOSS (C + E - D): &nbsp; 
                                                <span>{{ number_format($netProfit, 2) }}</span>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        </div>

                        <!-- Modern Excel & PDF Download Buttons at Bottom Left -->
                        <div class="no-print d-flex align-items-center mt-3 mb-2" style="gap: 15px; justify-content: flex-start; padding-left: 8%;">
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
    </main>

    @include('admin.includes.date_field')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
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
                
                var companyName = "{{ isset($setting) ? $setting->company_name : 'Electronics POS' }}";
                var address = "{{ isset($setting) ? $setting->address : '' }}";
                var contact = "{{ isset($setting) ? $setting->phone : '' }}";
                var reportTitle = 'Income Statement (Profit & Loss)';
                var dateInfo = 'For the period: {{ date("d M, Y", strtotime($from)) }} to {{ date("d M, Y", strtotime($to)) }}';

                var colspanVal = 2;

                var newRowsHtml = `
                    <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                    <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                    <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                    <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                    <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${dateInfo}</td></tr>
                    <tr><td colspan="${colspanVal}" style="border: none; height: 10px;"></td></tr>
                `;
                
                var tempDiv = document.createElement('tbody');
                tempDiv.innerHTML = newRowsHtml;
                var parent = clonedTable.querySelector('tbody');
                if(parent) {
                    while (tempDiv.firstChild) { parent.insertBefore(tempDiv.firstChild, parent.firstChild); }
                }

                var sheet = XLSX.utils.table_to_sheet(clonedTable, { raw: false });

                var range = XLSX.utils.decode_range(sheet['ref'] || sheet['!ref']);
                var colWidths = [
                    { wch: 45 },  // Line Item
                    { wch: 20 }   // Amount
                ];
                sheet['!cols'] = colWidths;

                var headerRowCount = 6;

                for (var R = range.s.r; R <= range.e.r; ++R) {
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        var cell_ref = XLSX.utils.encode_cell({c: C, r: R});
                        var cell = sheet[cell_ref];
                        if (cell && cell.v !== undefined) {
                            
                            cell.s = { font: { name: 'Arial', sz: 11 }, alignment: { vertical: "center" } };

                            if (R < headerRowCount) {
                                cell.s.alignment.horizontal = "center";
                                cell.s.font.bold = true;
                                if (R === 0) cell.s.font.sz = 14;
                            } else {
                                var valStr = (cell.v + '').trim();
                                var isHeading = valStr.match(/^[A-F]\./) || valStr.includes('Revenue') || valStr.includes('COGS') || valStr.includes('Expenses') || valStr.includes('Income');
                                var isTotal = valStr.includes('Net Sales') || valStr.includes('Total Cost') || valStr.includes('GROSS PROFIT') || valStr.includes('Total Operating') || valStr.includes('Total Other') || valStr.includes('NET PROFIT');

                                if (isHeading) {
                                    cell.s.font.bold = true;
                                    cell.s.font.sz = 12;
                                    cell.s.fill = { fgColor: { rgb: "F2F2F2" } };
                                } else if (isTotal) {
                                    cell.s.font.bold = true;
                                    cell.s.border = { top: {style: "thin"}, bottom: {style: "double"} };
                                }

                                if (C === 1) {
                                    cell.s.alignment.horizontal = "right";
                                    var numStr = valStr.replace(/,/g, '').replace(/[+\-\s]/g, '').trim();
                                    if (numStr !== '' && !isNaN(numStr)) {
                                        cell.t = 'n';
                                        cell.v = parseFloat(numStr) * (valStr.includes('-') ? -1 : 1);
                                        cell.z = '#,##0.00';
                                    }
                                } else {
                                    cell.s.alignment.horizontal = "left";
                                }
                            }
                        }
                    }
                }

                var wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, sheet, "Profit & Loss Statement");
                
                var safeFileName = 'Profit_Loss_Statement_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
            iframe.style.width = '900px';
            iframe.style.height = '1200px';
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
                        body { font-family: Arial, sans-serif; font-size: 12px; background: #fff; margin: 0; padding: 30px; color: #000; }
                        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 15px; }
                        th, td { border-bottom: 1px solid #dee2e6; padding: 10px 12px; vertical-align: top; color: #000; }
                        .pl-heading { background-color: #f8f9fa !important; font-weight: bold; font-size: 1.15em; }
                        .pl-sub-item { padding-left: 25px !important; }
                        .pl-total { font-weight: bold; background-color: #e9ecef !important; }
                        .pl-final-total { font-size: 1.3em; font-weight: bold; text-align: right; padding: 15px !important; border-top: 3px solid #dee2e6; border-bottom: 3px double #dee2e6; }
                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        .text-left { text-align: left; }
                        .text-success { color: #28a745 !important; }
                        .text-danger { color: #dc3545 !important; }
                        .text-primary { color: #007bff !important; }
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
                            var opt = {
                                margin:       [0.5, 0.5, 0.5, 0.5],
                                filename:     'Profit_Loss_Statement_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_profit_loss', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_profit_loss') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
@endsection
