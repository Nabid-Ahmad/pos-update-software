
@extends('admin.master')
@section('title', ' - Supplier Wise Discount Report')

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
                        <div class="row d-print-none">
                            <div class="col-md-11 mx-auto">
                                <div class="table-responsive">

                                    <form action="" method="get">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td width="30%">
                                                    <label>Supplier</label>
                                                    <select name="fk_supplier_id" class="form-control select2">
                                                        <option value="">Select</option>
                                                        @foreach ($suppliers as $id => $name)
                                                            <option value="{{ $id }}" {{ $id == request()->fk_supplier_id ? 'selected':'' }}>{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="20%" class="no-print">
                                                    <label class="control-label">Start Date</label>
                                                    <input type="text" class="form-control dateField" name="from"
                                                           value="{{ request()->get('from') ?? date('Y-m-d') }}"
                                                           autocomplete="off">
                                                </td>

                                                <td width="20%" class="no-print">
                                                    <label class="control-label">End Date</label>
                                                    <input type="text" class="form-control dateField" name="to"
                                                           value="{{ request()->get('to') ?? date('Y-m-d') }}"
                                                           autocomplete="off">
                                                </td>


                                                <td width="20%" class="no-print text-right">

                                                    <div class="form-group" style="margin-top: 26px;">
                                                        <button class="btn btn-primary"><i
                                                                class="fa fa-check"></i> Check
                                                        </button>
                                                        <a href="{{ url('reports/sale_discounts') }}" class="btn btn-danger"><i
                                                                class="fa fa-refresh"></i></a>

                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-11 mx-auto">
                                <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                    <thead>
                                        <tr>
                                            <td colspan="9" class="text-center py-2" style="border: none !important;">
                                                @include('partials._print_header')
                                                <h4>Supplier Wise Discount Report</h4>
                                                @if (request('from'))
                                                     <p class="mb-1">
                                                         Showing sale discounts From <b>{{request('from')}}</b>
                                                         to <b>{{request('to')}}</b>
                                                     </p>
                                                 @endif
                                                 @if (request()->filled('fk_supplier_id'))
                                                     <p class="mb-0">
                                                         Supplier: <b>{{ $suppliers->get(request('fk_supplier_id')) }}</b>
                                                     </p>
                                                 @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="4%">Sl</th>
                                            <th>Date</th>
                                            <th>Invoice ID </th>
                                            <th class="text-right">Total Price</th>
                                            <th class="text-right">Discount</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @isset($sale_discounts)
                                            @php
                                                $totalPrice = 0;
                                                $totalDiscount = 0;
                                            @endphp
                                            <tbody>
                                                @foreach($sale_discounts as $key => $sale_discounts_info)
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($sale_discounts_info->sale_date)->format('d-m-Y') }}</td>
                                                        <td style="text-align: center"><a href="{{ route('sales.show', $sale_discounts_info->id) }}?print_type=invoice" target="_blank">{{ $sale_discounts_info->invoiceId }}</a></td>
                                                        <td class="text-right">{{ $sale_discounts_info->total_payable ?? 0 }}</td>
                                                        <td class="text-right">{{ $invoice_discount = $sale_discounts_info->invoice_discount ?? 0 }}</td>
                                                    </tr>
                                                    @php
                                                        $totalPrice += $sale_discounts_info->total_payable;
                                                        $totalDiscount += $invoice_discount;
                                                    @endphp
                                                @endforeach
                                            </tbody>

                                            <tfoot>
                                                <tr class="font-weight-bold">
                                                    <td colspan="3">Total: </td>
                                                    <td class="text-right">{{ number_format($totalPrice, 2) }}</td>
                                                    <td class="text-right">{{ number_format($totalDiscount, 2) }}</td>
                                                </tr>
                                            </tfoot>
                                        @endisset
                                    </tbody>
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
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script type="text/javascript">
        $('.select2').select2();

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
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Supplier Wise Discount Report';
                    
                    var subtitleInfo = '';
                    var pTags = titleCell.querySelectorAll('p');
                    pTags.forEach(function(pt) {
                        subtitleInfo += pt.innerText.replace(/\s+/g, ' ').trim() + ' | ';
                    });
                    if (subtitleInfo.endsWith(' | ')) subtitleInfo = subtitleInfo.slice(0, -3);

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 5;

                    var newRowsHtml = `
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                        ${subtitleInfo ? `<tr><td colspan="${colspanVal}" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${subtitleInfo}</td></tr>` : ''}
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
                    { wch: 6 },   // Sl
                    { wch: 15 },  // Date
                    { wch: 18 },  // Invoice ID
                    { wch: 18 },  // Total Price
                    { wch: 18 }   // Discount
                ];
                sheet['!cols'] = colWidths;

                var headerRowCount = 5;
                if(subtitleInfo) headerRowCount++;

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
                            } else if (R === headerRowCount || R === range.e.r) {
                                cell.s.font.bold = true;
                                cell.s.alignment.horizontal = "center";
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if(C >= 3) cell.s.alignment.horizontal = "right";
                            } else {
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C === 0 || C === 1 || C === 2) cell.s.alignment.horizontal = "center";
                                else if (C >= 3) cell.s.alignment.horizontal = "right";
                                else cell.s.alignment.horizontal = "left";
                            }

                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'Sl' || valStr.includes('Total:')) continue;
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal) && C >= 3) {
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
                XLSX.utils.book_append_sheet(wb, sheet, "Discount Report");
                
                var safeFileName = 'Supplier_Wise_Discount_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
                                filename:     'Supplier_Wise_Discount_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_discount', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_discount') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
    @include('admin.includes.date_field')
@endsection
