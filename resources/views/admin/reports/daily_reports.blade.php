@extends('admin.master')
@section('title', ' - Daily Report')

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
                    <div id="report-wrapper">
                        <!-- print header & print button -->
                        <div class="row">
                            <div class="col-sm-12">
                                @include('partials._print_header')
                                <div class="text-center mb-4">
                                    <h4 style="font-weight: 600; color: #2b3a4a; margin-bottom: 5px;">Daily Sales & Purchase Report</h4>
                                    <p style="font-size: 14px; color: #6c757d; margin-bottom: 0;">Date: {{ date('d F Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="tile-body">

                        <!-- daily sales report -->
                        <div class="row mt-2">
                            <div style="width: 94% !important; margin-left: 3% !important;">
                                <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                    <thead>
                                        <tr>
                                            <td colspan="9" class="text-center py-2" style="border: none !important;">
                                                <h5>Daily Sales Report </h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="1%">SL</th>
                                            <th>Product Name</th>
                                            <th width="20%" class="sales text-right" style="padding-right: 10px">Per Unit</th>
                                            <th width="20%" class="sales text-center">Quantity</th>
                                            <th width="20%" class="sales text-right" style="padding-right: 10px">Total Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @php
                                            $totalQty = 0;
                                            $totalAmount = 0;
                                        @endphp
                                        @foreach($todaySales as $key => $product)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $product->product_name }}</td>
                                                <td class="text-right" style="padding-right: 10px">{{ $product->product_price }}</td>
                                                <td class="text-center">{{ $product->todaySoldQuantity() }}</td>
                                                <td class="text-right" style="padding-right: 10px">{{ number_format($product->todaySoldAmount(), 2) }}</td>
                                            </tr>
                                            @php
                                                $totalQty += $product->todaySoldQuantity();
                                                $totalAmount += $product->todaySoldAmount();
                                            @endphp
                                        @endforeach
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <th colspan="3"><strong>Total:</strong></th>
                                            <th class="text-center">{{ $totalQty }}</th>
                                            <th class="text-right" style="padding-right: 10px">{{ number_format($totalAmount, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- daily purchase report -->
                        <div class="row" style="margin-top: 20px">
                            <div style="width: 94% !important; margin-left: 3% !important;">
                                <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                    <thead>
                                        <tr>
                                            <td colspan="9" class="text-center py-2" style="border: none !important;">
                                                <h5>Daily Purchase Report </h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="1%">SL</th>
                                            <th>Product Name</th>
                                            <th width="15%" class="text-right" style="padding-right: 10px">Per Unit</th>
                                            <th width="20%" class="purchase text-center">Quantity</th>
                                            <th width="20%" class="text-right" style="padding-right: 10px">Total Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @php
                                            $totalQty = 0;
                                            $totalAmount = 0;
                                        @endphp
                                        @foreach($todayPurchases as $key => $product)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $product->product_name }}</td>
                                                <td class="text-right" style="padding-right: 10px">{{ number_format(optional($product->todayPurchased->first())->unit_cost ?? $product->product_cost, 2) }}</td>
                                                <td class="text-center">{{ $product->todayPurchaseQuantity() }}</td>
                                                <td class="text-right" style="padding-right: 10px">{{ number_format($product->todayPurchaseAmount(), 2) }}</td>
                                            </tr>
                                            @php
                                                $totalQty += $product->todayPurchaseQuantity();
                                                $totalAmount += $product->todayPurchaseAmount();
                                            @endphp
                                        @endforeach
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <th colspan="3"><strong>Total:</strong></th>
                                            <th class="text-center">{{ $totalQty }}</th>
                                            <th class="text-right" style="padding-right: 10px">{{ number_format($totalAmount, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>

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
                    </div> <!-- closes report-wrapper -->
                </div> <!-- closes tile -->
            </div>
        </div>
    </main>
@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script type="text/javascript">
        function downloadExcelDirect() {
            var tables = document.querySelectorAll('#stock_table');
            if (tables.length < 2) return;
            var salesTable = tables[0];
            var purchaseTable = tables[1];

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
                var reportTitle = 'Daily Sales & Purchase Report';
                var dateInfo = titleCell && titleCell.querySelector('p') ? titleCell.querySelector('p').innerText.replace(/\s+/g, ' ').trim() : '';

                var salesRows = Array.from(salesTable.querySelectorAll('tr'));
                var purchaseRows = Array.from(purchaseTable.querySelectorAll('tr'));
                var maxRows = Math.max(salesRows.length, purchaseRows.length);

                var virtualTable = document.createElement('table');
                var tbody = document.createElement('tbody');
                virtualTable.appendChild(tbody);

                var newRowsHtml = `
                    <tr><td colspan="11" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                    <tr><td colspan="11" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                    <tr><td colspan="11" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                    <tr><td colspan="11" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                    ${dateInfo ? `<tr><td colspan="11" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${dateInfo}</td></tr>` : ''}
                    <tr><td colspan="11" style="border: none; height: 10px;"></td></tr>
                `;
                tbody.innerHTML = newRowsHtml;

                for (var i = 0; i < maxRows; i++) {
                    var tr = document.createElement('tr');
                    
                    // Sales table cells
                    if (i < salesRows.length) {
                        var cells = Array.from(salesRows[i].cells);
                        cells.forEach(function(c) {
                            var newCell = c.cloneNode(true);
                            if (newCell.getAttribute('colspan') === '9') {
                                newCell.setAttribute('colspan', '5');
                            }
                            tr.appendChild(newCell);
                        });
                    } else {
                        tr.innerHTML += '<td></td><td></td><td></td><td></td><td></td>';
                    }

                    // Separator column (Col F / Index 5)
                    var sepCell = document.createElement('td');
                    sepCell.style.width = '30px';
                    sepCell.style.border = 'none';
                    tr.appendChild(sepCell);

                    // Purchase table cells
                    if (i < purchaseRows.length) {
                        var cells = Array.from(purchaseRows[i].cells);
                        cells.forEach(function(c) {
                            var newCell = c.cloneNode(true);
                            if (newCell.getAttribute('colspan') === '9') {
                                newCell.setAttribute('colspan', '5');
                            }
                            tr.appendChild(newCell);
                        });
                    } else {
                        tr.innerHTML += '<td></td><td></td><td></td><td></td><td></td>';
                    }

                    tbody.appendChild(tr);
                }

                var sheet = XLSX.utils.table_to_sheet(virtualTable, { raw: false });

                var range = XLSX.utils.decode_range(sheet['ref'] || sheet['!ref']);
                var colWidths = [
                    { wch: 6 },   // Sales SL
                    { wch: 25 },  // Product Name
                    { wch: 15 },  // Per Unit
                    { wch: 12 },  // Qty
                    { wch: 15 },  // Total Amount
                    { wch: 4 },   // Separator
                    { wch: 6 },   // Purchase SL
                    { wch: 25 },  // Product Name
                    { wch: 15 },  // Per Unit
                    { wch: 12 },  // Qty
                    { wch: 15 }   // Total Amount
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
                            } else if (C === 5) {
                                // Separator column
                                cell.s.border = {};
                            } else {
                                // Table data styling
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                
                                var valStr = (cell.v + '').replace(/,/g, '').trim();

                                if (valStr.includes('Daily Sales') || valStr.includes('Daily Purchase') || valStr === 'SL' || valStr === 'Product Name' || valStr === 'Per Unit' || valStr === 'Quantity' || valStr === 'Total Amount' || valStr.includes('Total:')) {
                                    cell.s.font.bold = true;
                                    cell.s.alignment.horizontal = "center";
                                    if (valStr.includes('Total:')) {
                                        cell.s.alignment.horizontal = "right";
                                    }
                                } else {
                                    if (C === 0 || C === 3 || C === 6 || C === 9) cell.s.alignment.horizontal = "center";
                                    else if (C === 2 || C === 4 || C === 8 || C === 10) cell.s.alignment.horizontal = "right";
                                    else cell.s.alignment.horizontal = "left";
                                }

                                if (valStr !== '' && !isNaN(valStr) && valStr !== 'SL' && !valStr.includes('Total:')) {
                                    var numVal = parseFloat(valStr);
                                    if (!isNaN(numVal)) {
                                        if (C === 2 || C === 4 || C === 8 || C === 10) {
                                            cell.t = 'n';
                                            cell.v = numVal;
                                            cell.z = '#,##0.00';
                                        } else if (C === 0 || C === 3 || C === 6 || C === 9) {
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
                XLSX.utils.book_append_sheet(wb, sheet, "Daily Report");
                
                var safeFileName = 'Daily_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
                                filename:     'Daily_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_daily', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_daily') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
@endsection
@endsection
