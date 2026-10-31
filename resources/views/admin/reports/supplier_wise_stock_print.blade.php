@extends('admin.master')
@section('title', ' - Supplier Wise Stock Report')

@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="table-responsive">
                        <form id="reportForm">
                            <table class="table table-sm no-print table-borderless">
                                <tr>
                                    <td width="5%"></td>
                                    <td width="20%" class="no-print ">
                                        <label class="control-label">Supplier Name</label>
                                        <select name="fk_supplier_id" class="form-control">
                                            <option value="">Select</option>
                                            @foreach ($suppliers as $id => $name)
                                                <option value="{{ $id }}" {{ $id == request()->fk_supplier_id ? 'selected':'' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="20%" class="no-print">
                                        <label class="control-label">Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ request()->start_date }}">
                                    </td>
                                    <td width="20%" class="no-print">
                                        <label class="control-label">End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="{{ request()->end_date }}">
                                    </td>
                                    <td width="20%" class="no-print">
                                        <button class="btn btn-primary" style="margin-top: 26px;">
                                            <i class="fa fa-check"></i> Check
                                        </button>
                                        <a class="btn btn-success" style="margin-top:26px;" href="{{ route('supplier.wise.stock.reports.print').'?fk_supplier_id='.request()->fk_supplier_id.'&start_date='.request()->start_date.'&end_date='.request()->end_date }}" role="button">
                                            <i class="fa fa-print"></i>Print
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>

                    <div class="tile-body">
                        <div class="table-responsive table-sm">
                            <div id="report-wrapper">
                            <table class="table table-hover table-bordered" id="stock_table" style="border: none !important;">
                                <thead>
                                    <tr>
                                        <td colspan="13" class="text-center py-2" style="border: none !important; display: none;">
                                            @include('partials._print_header')
                                            <h4>Supplier Wise Stock Report</h4>
                                            @if ($supplier)
                                                <p>
                                                    Supplier:
                                                    <span style="font-weight:bold">{{$supplier->name ?? '' }} ({{$supplier->company_name ?? '' }})
                                                        <br></span>
                                                    {{$supplier->phone ?? '' }}, {{$supplier->email ?? '' }}
                                                </p>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="no-print">
                                        <td colspan="13" class="text-center py-2" style="border: none !important;">
                                            <h4>Supplier Wise Stock Report</h4>
                                            @if ($supplier)
                                                <p>
                                                    Supplier:
                                                    <span style="font-weight:bold">{{$supplier->name ?? '' }} ({{$supplier->company_name ?? '' }})
                                                        <br></span>
                                                    {{$supplier->phone ?? '' }}, {{$supplier->email ?? '' }}
                                                </p>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="1%">Sl</td>
                                        <td width="10%">Product Code</td>
                                        <td>Product Name</td>
                                        <td>Category</td>
                                        <td>Supplier</td>
                                        <td class="text-center">Opening Qty</td>
                                        <td class="text-center">Purchase Quantity</td>
                                        <td class="text-center" style="color: green;">Free Qty</td>
                                        <td class="text-center">Sold Qty</td>
                                        <td class="text-center" style="color: red;">Damaged Qty</td>
                                        <td class="text-center">Available Qty</td>
                                        <td class="text-right">Product Cost</td>
                                        <td class="text-right">Value</td>
                                    </tr>
                                </thead>

                                <tbody>
                                @php $total = 0 @endphp
                                    @foreach($stocks as $key => $stock)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ optional($stock->product)->product_code }}</td>
                                            <td>{{ optional($stock->product)->product_name }}</td>
                                            <td>{{ optional(optional($stock->product)->category_name)->category_name }}</td>
                                            <td>
                                                <span data-toggle="tooltip" data-placement="top" data-original-title="{{ optional(optional($stock->product)->supplier)->phone ?? '' }}">{{ optional(optional($stock->product)->supplier)->name ?? '' }} &nbsp; <small>({{ optional(optional($stock->product)->supplier)->company_name ?? '' }})</small></span>
                                            </td>
                                            <td class="opening_quantity text-center">{{ $stock->opening_quantity }}</td>
                                            <td class="purchased_quantity text-center">{{ $stock->purchased_quantity }}</td>
                                            <td class="free_quantity text-center" style="color: green;">{{ $stock->free_quantity }}</td>
                                            <td class="sold_quantity text-center">{{ $stock->sold_quantity }}</td>
                                            <td class="wastage_quantity text-center" style="color: red;">{{ $stock->wastage_quantity }}</td>
                                            <td class="available_quantity text-center">{{ $stock->available_quantity }}</td>
                                            <td class="product_cost text-right">{{ number_format(optional($stock->stock_product)->product_cost, 2) }}</td>
                                            <span class="total_price" hidden>{{ optional($stock->stock_product)->product_cost }}</span>
                                            <td class="available_quantity_totals text-right">{{ number_format(floatval(optional($stock->stock_product)->product_cost) * floatval($stock->available_quantity), 2)}}</td>
                                            @php $total += (floatval(optional($stock->stock_product)->product_cost) * floatval($stock->available_quantity)) @endphp
                                        </tr>
                                    @endforeach

                                    <tr class="font-weight-bold">
                                        <td colspan="5" class="text-left">Total This Page</td>
                                        <td id="opening_quantity" class="text-center"></td>
                                        <td id="purchased_quantity" class="text-center"></td>
                                        <td id="free_quantity" class="text-center" style="color: green;"></td>
                                        <td id="sold_quantity" class="text-center"></td>
                                        <td id="wastage_quantity" class="text-center" style="color: red;"></td>
                                        <td id="available_quantity" class="text-center"></td>
                                        <td></td>
                                        <td id="total_prices" class="text-right">{{ number_format($total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="12">Grand Total:</th>
                                        <th class="text-right">{{ number_format($totalProductPrice, 2) }}</th>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3 no-print">
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
                                <div>
                                    @include('admin.includes.pagination', ['data' => $stocks])
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
    <script type="text/javascript" src="{{asset('jq/select2Loads.js')}}"></script>
    <script>
        select2Loads({
            selector: '.supplier',
            url: "/people/suppliers",
        });


        getTotal('opening_quantity');
        getTotal('purchased_quantity');
        getTotal('free_quantity');
        getTotal('sold_quantity');
        getTotal('wastage_quantity');
        getTotal('available_quantity');

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
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Supplier Wise Stock Report';
                    var supplierInfo = titleCell.querySelector('p') ? titleCell.querySelector('p').innerText : '';

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 13;

                    var newRowsHtml = `
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 11pt; border: none;">${supplierInfo}</td></tr>
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
                    { wch: 15 },  // Product Code
                    { wch: 25 },  // Product Name
                    { wch: 15 },  // Category
                    { wch: 25 },  // Supplier
                    { wch: 12 },  // Opening Qty
                    { wch: 15 },  // Purchase Quantity
                    { wch: 12 },  // Free Qty
                    { wch: 12 },  // Sold Qty
                    { wch: 15 },  // Damaged Qty
                    { wch: 15 },  // Available Qty
                    { wch: 15 },  // Product Cost
                    { wch: 15 }   // Value
                ];
                sheet['!cols'] = colWidths;

                for (var R = range.s.r; R <= range.e.r; ++R) {
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        var cell_ref = XLSX.utils.encode_cell({c: C, r: R});
                        var cell = sheet[cell_ref];
                        if (cell && cell.v !== undefined) {
                            
                            cell.s = { font: { name: 'Arial', sz: 10 }, alignment: { vertical: "center" } };

                            if (R < 5) {
                                cell.s.alignment.horizontal = "center";
                                cell.s.font.bold = true;
                                if (R === 0) cell.s.font.sz = 14;
                            } else if (R === 5) {
                                // empty
                            } else if (R === 6 || cell.v.toString().includes('Total') || cell.v.toString().includes('Grand Total')) {
                                cell.s.font.bold = true;
                                cell.s.alignment.horizontal = "center";
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C >= 11) cell.s.alignment.horizontal = "right";
                                if (C === 0 && (cell.v.toString().includes('Total') || cell.v.toString().includes('Grand Total'))) cell.s.alignment.horizontal = "right";
                            } else {
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C === 0 || (C >= 5 && C <= 10)) cell.s.alignment.horizontal = "center";
                                else if (C >= 11) cell.s.alignment.horizontal = "right";
                                else cell.s.alignment.horizontal = "left";
                            }

                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'Sl') continue;
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal) && C >= 11) {
                                cell.t = 'n';
                                cell.v = numVal;
                                cell.z = '#,##0.00'; 
                            } else if (!isNaN(numVal) && C >= 5 && C <= 10) {
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
                XLSX.utils.book_append_sheet(wb, sheet, "Supplier Stock");
                
                var safeFileName = 'Supplier_Wise_Stock_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
            iframe.style.width = '1400px';
            iframe.style.height = '1000px';
            document.body.appendChild(iframe);
            
            var clonedTable = element.cloneNode(true);
            var dPrintNoneElements = clonedTable.querySelectorAll('.d-print-none, .no-print');
            dPrintNoneElements.forEach(function(el) { el.parentNode.removeChild(el); });

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
                        body { font-family: Arial, sans-serif; font-size: 10px; background: #fff; margin: 0; padding: 15px; color: #000; }
                        table { width: 100%; border-collapse: collapse; background: #fff; }
                        th, td { border: 1px solid #dee2e6 !important; padding: 5px 6px; vertical-align: middle; color: #000; }
                        th { background-color: #f8f9fa; font-weight: bold; }
                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        .text-left { text-align: left; }
                        h3, h4, h5, p { text-align: center; margin: 3px 0; color: #000; }
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
                                margin:       [0.4, 0.4, 0.4, 0.4],
                                filename:     'Supplier_Wise_Stock_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_supplierstock', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_supplierstock') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }

        // Auto-print when redirected from report page with autoprint=1 param
        @if(request()->autoprint == '1')
        window.addEventListener('load', function() {
            // Hide the filter form at the top
            document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
            setTimeout(function() {
                window.print();
            }, 800);
        });
        @endif
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@endsection

