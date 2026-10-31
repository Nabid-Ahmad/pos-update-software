@extends('admin.master')
@section('title', ' - Expire Products Report')

@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">

                    <!-- Filter -->
                    <div class="row d-print-none">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <form method="get">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td width="15%">
                                                <label>Category</label>
                                                <select name="fk_category_id" class="form-control select2">
                                                    <option value="">Select</option>
                                                    @foreach ($categories as $id => $name)
                                                        <option value="{{ $id }}" {{ $id == request('fk_category_id') ? 'selected' : '' }}>{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td width="15%">
                                                <label>Supplier</label>
                                                <select name="fk_supplier_id" class="form-control select2">
                                                    <option value="">Select</option>
                                                    @foreach ($suppliers as $id => $name)
                                                        <option value="{{ $id }}" {{ $id == request('fk_supplier_id') ? 'selected' : '' }}>{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td width="20%" class="no-print">
                                                <label class="control-label">For Date</label>
                                                <input type="text" class="form-control dateField" name="date" value="{{ request('date') }}" autocomplete="off">
                                            </td>


                                            <td width="20%" class="no-print">

                                                <div class="form-group" style="margin-top: 26px;">
                                                    <button class="btn btn-primary">
                                                        <i class="fa fa-check"></i> Check
                                                    </button>
                                                    <a href="{{ route('expire.products') }}" class="btn btn-danger"><i class="fa fa-refresh"></i></a>
                                                    <button type="button" class="btn btn-success" onclick="window.print()">
                                                        <i class="fa fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="tile-body">
                        <div id="report-wrapper">
                            <table class="table table-hover table-bordered" id="stock_table">
                                <thead>
                                    <tr>
                                        <td colspan="9" class="text-center py-2" style="border: none !important;">
                                            <h4>Expire Products Report</h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="1%">Sl</td>
                                        <td>Product Name</td>
                                        <td>Supplier</td>
                                        <td>Category</td>
                                        <td class="text-center">Available Qty</td>
                                        <td class="text-center">Valid Qty</td>
                                        <td class="text-center">Expire Qty</td>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($stocks as $key => $stock)
                                        @php
                                            $availableQty = $stock->available_quantity;
                                            $validQty     = $stock->total_valid_quantity;
                                            $expireQty    = $availableQty - $validQty
                                        @endphp
                                        @if($expireQty > 0)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ optional($stock->product)->product_name }}</td>
                                                <td>{{ optional(optional($stock->product)->supplier)->name }}</td>
                                                <td>{{ optional(optional($stock->product)->category_name)->category_name }}</td>
                                                <td class="available_quantity text-center">{{ $availableQty }}</td>
                                                <td class="available_quantity text-center">{{ $validQty ?? 0 }}</td>
                                                <td class="available_quantity text-center">{{ $expireQty ?? 0 }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

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
    </main>
@endsection

@section('footer-script')
    <script type="text/javascript" src="{{asset('jq/select2Loads.js')}}"></script>
    <script>
        $('.expire-date').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            todayHighlight: true,
        });

        select2Loads({
            selector: '.supplier',
            url: "/people/suppliers",
        });


        getTotal('available_quantity');
        getTotal('purchased_quantity');
        getTotal('sold_quantity');
        getTotal('wastage_quantity');
        getTotal('available_quantity_total');
        getTotal('total_price');

        function getTotal(className){
            var total = 0;
            $('.'+className).each(function(){
                field_value = $(this).text() != NaN ? parseFloat($(this).text()):0;
                total += field_value;
            });

            $('#'+className).text(amountFormat(total));
        }

        function amountFormat(amount) {
            return (amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')
        }
    </script>
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
                
                var titleRow = clonedTable.querySelector('thead tr:first-child');
                if (titleRow && titleRow.querySelector('td[colspan], th[colspan]')) {
                    var titleCell = titleRow.querySelector('td[colspan], th[colspan]');
                    
                    var companyName = titleCell.querySelector('h3') ? titleCell.querySelector('h3').innerText : '';
                    var address = titleCell.querySelectorAll('h5')[0] ? titleCell.querySelectorAll('h5')[0].innerText : '';
                    var contact = titleCell.querySelectorAll('h5')[1] ? titleCell.querySelectorAll('h5')[1].innerText : '';
                    var reportTitle = 'Expire Products Report';

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = 7;

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

                var range = XLSX.utils.decode_range(sheet['ref'] || sheet['!ref']);
                var colWidths = [
                    { wch: 8 },   // Sl
                    { wch: 35 },  // Product Name
                    { wch: 25 },  // Supplier
                    { wch: 25 },  // Category
                    { wch: 15 },  // Available Qty
                    { wch: 15 },  // Valid Qty
                    { wch: 15 }   // Expire Qty
                ];
                sheet['!cols'] = colWidths;

                var headerRowCount = 5;

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
                            } else if (R === headerRowCount) {
                                cell.s.font.bold = true;
                                cell.s.alignment.horizontal = "center";
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                            } else {
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C === 0 || C >= 4) cell.s.alignment.horizontal = "center";
                                else cell.s.alignment.horizontal = "left";
                            }

                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'Sl') continue;
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal) && C >= 4) {
                                cell.t = 'n';
                                cell.v = numVal;
                                cell.z = '0';
                            } else if (!isNaN(numVal) && C === 0) {
                                cell.t = 'n';
                                cell.v = numVal;
                                cell.z = '0';
                            }
                        }
                    }
                }

                var wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, sheet, "Expire Products Report");
                
                var safeFileName = 'Expire_Products_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
                                filename:     'Expire_Products_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_expire', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_expire') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
@endsection
