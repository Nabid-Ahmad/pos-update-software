
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

                                <div class="table-responsive px-3">
                                    <h3>Product Name: {{ $product->product_name }} <span class="badge badge-success">{{ $product->product_serials->count() }} {{ optional($product->product_unit)->name }}</span></h3>
                                    <h4>Product Code: {{ $product->product_code }}</h4>
                                    <h4>Product Categpry: {{ optional($product->category)->name }}</h4>

                                    <br>
                                    <h5>Product Serials:</h5>
                                    <hr>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 pl-4">
                                <div id="report-wrapper">
                                <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                    <thead>
                                        <tr>
                                            <td colspan="4" class="text-center py-2" style="border: none !important; display: none;">
                                                @include('partials._print_header')
                                                <h4>Product Serial Report</h4>
                                                <h5>Product: {{ $product->product_name }} ({{ $product->product_code }})</h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Sl.</th>
                                            <th>Source</th>
                                            <th>Serial</th>
                                            <th>Expire Date</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($product->product_serials as $key => $serial)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $serial->source }}</td>
                                                <td>{{ $serial->serial }}</td>
                                                <td>{{ $serial->is_sold == 1 ? date('Y-m-d', strtotime($serial->created_at)) : '' }}</td>
                                            </tr>
                                        @endforeach
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

                                    <span class="badge badge-primary p-2" style="background: #52696f !important; font-size: 13px"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script type="text/javascript">
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
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Product Serial Report';
                    var productInfo = titleCell.querySelectorAll('h5')[2] ? titleCell.querySelectorAll('h5')[2].innerText : '';

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 4;

                    var newRowsHtml = `
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${productInfo}</td></tr>
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
                    { wch: 8 },   // Sl
                    { wch: 25 },  // Source
                    { wch: 25 },  // Serial
                    { wch: 18 }   // Expire Date
                ];
                sheet['!cols'] = colWidths;

                for (var R = range.s.r; R <= range.e.r; ++R) {
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        var cell_ref = XLSX.utils.encode_cell({c: C, r: R});
                        var cell = sheet[cell_ref];
                        if (cell && cell.v !== undefined) {
                            
                            cell.s = { font: { name: 'Arial', sz: 11 }, alignment: { vertical: "center" } };

                            if (R < 5) {
                                cell.s.alignment.horizontal = "center";
                                cell.s.font.bold = true;
                                if (R === 0) cell.s.font.sz = 14;
                            } else if (R === 5) {
                                // empty
                            } else if (R === 6) {
                                cell.s.font.bold = true;
                                cell.s.alignment.horizontal = "center";
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                            } else {
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C === 0) cell.s.alignment.horizontal = "center";
                                else cell.s.alignment.horizontal = "left";
                            }

                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'Sl.') continue;
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal) && C === 0) {
                                cell.t = 'n';
                                cell.v = numVal;
                                cell.z = '0';
                            }
                        }
                    }
                }

                var wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, sheet, "Serial Report");
                
                var safeFileName = 'Product_Serial_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
                        tr { page-break-inside: avoid; break-inside: avoid; }
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
                                filename:     'Product_Serial_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' },
                                pagebreak:    { mode: ['avoid-all', 'css'] }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_serials', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_serials') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
