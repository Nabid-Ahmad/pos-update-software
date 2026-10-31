
@extends('admin.master')
@section('title', ' - Supplier Wise Return Report')

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
                        <div class="row">
                            <div class="col-md-11 mx-auto">
                                <div class="table-responsive">

                                    <form action="{{ url('reports/sale-returns/') }}" method="get">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td width="25%">
                                                    <label>Supplier</label>
                                                    <select name="fk_supplier_id" class="form-control">
                                                        <option value="">Select Supplier</option>
                                                        @foreach ($suppliers as $id => $name)
                                                            <option value="{{ $id }}" {{ $id == request()->fk_supplier_id ? 'selected':'' }}>{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="25%" class="no-print">
                                                    <label class="control-label">Start Date</label>
                                                    <input type="text" class="form-control dateField" name="start_date"
                                                           value="{{ request()->get('start_date') ?? date('Y-m-d') }}"
                                                           autocomplete="off">
                                                </td>

                                                <td width="25%" class="no-print">
                                                    <label class="control-label">End Date</label>
                                                    <input type="text" class="form-control dateField" name="end_date"
                                                           value="{{ request()->get('end_date') ?? date('Y-m-d') }}"
                                                           autocomplete="off">
                                                </td>


                                                <td width="25%" class="no-print text-right">

                                                    <div class="form-group" style="margin-top: 26px;">
                                                        <button class="btn btn-primary"><i
                                                                class="fa fa-check"></i> Check
                                                        </button>
                                                        <a href="{{ url('reports/sale-returns') }}" class="btn btn-danger"><i
                                                                class="fa fa-refresh"></i> Reset</a>
                                                    </div>

                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>

                        </div>
                        <div id="report-wrapper">
                        <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                            <thead>
                            <tr>
                                <td colspan="6" class="text-center py-2" style="border: none !important; display: none;">
                                    @include('partials._print_header')
                                    <h4>Supplier Wise Return Report</h4>
                                    @if (request('from'))
                                        <p>
                                            Showing sales From <b>{{request('from')}}</b>
                                            to <b>{{request('to')}}</b>
                                        </p>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th width="1%">#</th>
                                <th>Sale Reference</th>
                                <th>Date</th>
                                <th class="text-right">Total Payable</th>
                                <th class="text-right">Paid Amount</th>
                                <th class="text-right">Change</th>
                            </tr>
                            </thead>
                            <tbody>

                            @if(count($sale_returns) > 0)
                                @php
                                    $sl = 1;
                                    $total_return = 0;
                                    $total_paid = 0;
                                    $total_change = 0;
                                @endphp
                                @foreach($sale_returns as $sale_return)
                                        <tr>
                                            <td>{{ $sl++}}</td>
                                            <td>{{ optional($sale_return->sale)->sale_reference ?? 'N/A' }}</td>
                                            <td>{{ $sale_return->date->format('d-m-Y') }}</td>
                                            <td class="text-right">{{ number_format($return_amount = $sale_return->amount, 2) }}</td>
                                            <td class="text-right">{{ number_format($paid_amount = $sale_return->paid_amount, 2) }}</td>
                                            <td class="text-right">{{ number_format($change_amount = $sale_return->change_amount, 2) }}</td>
                                        </tr>
                                        @php
                                            $total_return += $return_amount;
                                            $total_paid += $paid_amount;
                                            $total_change += $change_amount;
                                        @endphp
                                @endforeach
                                <tr>
                                            <th colspan="3" class="text-right">Total :</th>
                                            <th class="text-right">{{ number_format($total_return, 2) }}</th>
                                            <th class="text-right">{{ number_format($total_paid, 2) }}</th>
                                            <th class="text-right">{{ number_format($total_change, 2) }}</th>
                                </tr>
                            @endif

                            </tbody>
                        </table>
                        </div>

                        <div class="no-print mt-4" style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; margin-bottom: 15px;">
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
                        {{-- @include('admin.includes.pagination', ['data' => $sale_returns]) --}}
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('footer-script')
    <!-- Data table plugin-->
    <script type="text/javascript" src="{{ asset('assets/admin/js/plugins/jquery.dataTables.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('assets/admin/js/plugins/dataTables.bootstrap.min.js')}}"></script>

    <script src="{{ asset('assets/admin/js/plugins/select2.min.js')}}"></script>

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
                
                var dPrintNoneElements = clonedTable.querySelectorAll('.d-print-none, .no-print');
                dPrintNoneElements.forEach(function(el) { el.parentNode.removeChild(el); });

                var titleRow = clonedTable.querySelector('thead tr:first-child');
                if (titleRow && titleRow.querySelector('td[colspan], th[colspan]')) {
                    var titleCell = titleRow.querySelector('td[colspan], th[colspan]');
                    
                    var companyName = titleCell.querySelector('h3') ? titleCell.querySelector('h3').innerText : '';
                    var address = titleCell.querySelectorAll('h5')[0] ? titleCell.querySelectorAll('h5')[0].innerText : '';
                    var contact = titleCell.querySelectorAll('h5')[1] ? titleCell.querySelectorAll('h5')[1].innerText : '';
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Supplier Wise Return Report';

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 6;

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

                var range = XLSX.utils.decode_range(sheet['!ref']);
                var colWidths = [
                    { wch: 6 },   // #
                    { wch: 20 },  // Sale Reference
                    { wch: 15 },  // Date
                    { wch: 18 },  // Total Payable
                    { wch: 18 },  // Paid Amount
                    { wch: 15 }   // Change
                ];
                sheet['!cols'] = colWidths;

                for (var R = range.s.r; R <= range.e.r; ++R) {
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        var cell_ref = XLSX.utils.encode_cell({c: C, r: R});
                        var cell = sheet[cell_ref];
                        if (cell && cell.v !== undefined) {
                            
                            cell.s = { font: { name: 'Arial', sz: 11 }, alignment: { vertical: "center" } };

                            if (R < 4) {
                                cell.s.alignment.horizontal = "center";
                                cell.s.font.bold = true;
                                if (R === 0) cell.s.font.sz = 14;
                            } else if (R === 4) {
                                // empty
                            } else if (R === 5 || cell.v.toString().includes('Total')) {
                                cell.s.font.bold = true;
                                cell.s.alignment.horizontal = "center";
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C >= 3) cell.s.alignment.horizontal = "right";
                                if (C === 0 && cell.v.toString().includes('Total')) cell.s.alignment.horizontal = "right";
                            } else {
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C === 0 || C === 2) cell.s.alignment.horizontal = "center";
                                else if (C >= 3) cell.s.alignment.horizontal = "right";
                                else cell.s.alignment.horizontal = "left";
                            }

                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === '#') continue;
                            
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
                XLSX.utils.book_append_sheet(wb, sheet, "Return Report");
                
                var safeFileName = 'Supplier_Wise_Return_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
                                filename:     'Supplier_Wise_Return_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' },
                                pagebreak:    { mode: ['avoid-all', 'css'] }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_return', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_return') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>

    @include('admin.includes.date_field')

    @include('admin.includes.delete_confirm')
@endsection
