
@extends('admin.master')
@section('title', ' - Wastage Report')

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
                        <!-- Filter -->
                        <div class="row d-print-none">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <form method="get">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td width="15%">
                                                    <label>Customer</label>
                                                    <select name="fk_customer_id" class="form-control select2">
                                                        <option value="">Select</option>
                                                        @foreach ($customers as $id => $name)
                                                            <option value="{{ $id }}" {{ $id == request()->fk_customer_id ? 'selected':'' }}>{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>

                                                <td width="15%">
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
                                                    <input type="text" class="form-control dateField" name="from" value="{{ request()->get('from') ?? date('Y-m-d') }}" autocomplete="off">
                                                </td>

                                                <td width="20%" class="no-print">
                                                    <label class="control-label">End Date</label>
                                                    <input type="text" class="form-control dateField" name="to" value="{{ request()->get('to') ?? date('Y-m-d') }}" autocomplete="off">
                                                </td>


                                                <td width="20%" class="no-print">

                                                    <div class="form-group" style="margin-top: 26px;">
                                                        <button class="btn btn-primary">
                                                            <i class="fa fa-check"></i> Check
                                                        </button>
                                                        <a href="{{ url('reports/wastages') }}" class="btn btn-danger"><i class="fa fa-refresh"></i></a>
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

                        <!-- display data to table -->
                        <div class="tile-body table-responsive table-sm d-print-none">
                            <table class="table table-hover table-bordered" id="sampleTable" style="border: none !important;">
                                <thead>
                                    <tr>
                                        <td colspan="9" class="text-center py-2" style="border: none !important;">
                                            <h4>Wastage / Damage Report</h4>
                                            @if (request('from'))
                                                <p>
                                                    Showing Wastage / Damage From <b>{{request('from')}}</b>
                                                    to <b>{{request('to')}}</b>
                                                </p>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="1%">Sl.</th>
                                        <th>Date</th>
                                        <th>Product Name</th>
                                        <th>Reference</th>
                                        <th>Damaged From</th>
                                        <th>Description</th>
                                        <th style="text-align: center">Quantity</th>
                                        <th style="text-align: right; padding-right: 10px">Price</th>
                                    </tr>
                                </thead>

                                <tbody class="d-print-none">
                                    @foreach ($wastages as $key => $wastage)
                                        <tr>
                                            <td>{{ $key + $wastages->firstItem() }}</td>
                                            <td>{{ $wastage->created_at->format('Y-m-d') }}</td>
                                            <td>{{ $wastage->itemable->product->product_name}}</td>
                                            <td>{{ $wastage->itemable->reference ?? '' }}</td>
                                            <td>{{ $wastage->typeName()}}</td>
                                            <td>{{ $wastage->itemable->description ?? ''}}</td>
                                            <td class="quantity" style="text-align: center">{{ $wastage->quantity}}</td>
                                            <td class="price" style="text-align: right; padding-right: 10px">{{ number_format($wastage->itemable->amount ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
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
                                <div>
                                    @include('admin.includes.pagination', ['data' => $wastages])
                                </div>
                            </div>
                        </div>

                        <!-- display area for print -->
                        <div class="tile-body table-sm d-print">
                            <table class="table table-hover table-bordered" id="stock_table_full" style="border: none !important;">
                                <thead>
                                    <tr>
                                        <td colspan="9" class="text-center py-2" style="border: none !important;">
                                            @include('partials._print_header')
                                            <h4>Wastage Report</h4>
                                            @if (request('from'))
                                                <p>
                                                    Showing wastages From <b>{{request('from')}}</b>
                                                    to <b>{{request('to')}}</b>
                                                </p>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="1%">Sl.</th>
                                        <th>Date</th>
                                        <th>Product Name</th>
                                        <th>Reference</th>
                                        <th>Damaged From</th>
                                        <th>Description</th>
                                        <th style="text-align: center">Quantity</th>
                                        <th style="text-align: right; padding-right: 10px">Price</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach ($wastagesFull as $wastage)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ \Carbon\Carbon::parse($wastage->created_at)->format('Y-m-d') ?? $wastage->created_at->format('Y-m-d') }}</td>
                                            <td>{{ $wastage->itemable->product->product_name}}</td>
                                            <td>{{ $wastage->itemable->reference ?? '' }}</td>
                                            <td>{{ $wastage->typeName()}}</td>
                                            <td>{{ $wastage->itemable->description ?? ''}}</td>
                                            <td class="quantity" style="text-align: center">{{ $wastage->quantity}}</td>
                                            <td class="price" style="text-align: right; padding-right: 10px">{{ number_format($wastage->itemable->amount ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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



    <script>

//total quantity
    var total_quantity = 0;
    $('.quantity').each(function(){
        var quantity = $(this).text() != NaN ? $(this).text():0;
        total_quantity += parseFloat(quantity);
    });
    $('#total_quantity').val(total_quantity);

//total price
    var total_price = 0;
    $('.price').each(function(){
        var price = $(this).text() != NaN ? $(this).text():0;
        total_price += parseFloat(price);
    });
    $('#total_price').val(total_price);

        function downloadExcelDirect() {
            var originalTable = document.getElementById('stock_table_full');
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
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Wastage Report';
                    var filterInfo = titleCell.querySelector('p') ? titleCell.querySelector('p').innerText.replace(/\s+/g, ' ').trim() : '';

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 8;

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
                    { wch: 12 },  // Date
                    { wch: 25 },  // Product Name
                    { wch: 20 },  // Reference
                    { wch: 20 },  // Damaged From
                    { wch: 25 },  // Description
                    { wch: 12 },  // Quantity
                    { wch: 15 }   // Price
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
                            } else if (R === headerRowCount) {
                                cell.s.font.bold = true;
                                cell.s.alignment.horizontal = "center";
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if(C === 7) cell.s.alignment.horizontal = "right";
                            } else {
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C === 0 || C === 1) cell.s.alignment.horizontal = "left";
                                else if (C === 7) cell.s.alignment.horizontal = "right";
                                else cell.s.alignment.horizontal = "center";
                            }

                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'Sl.') continue;
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal) && C === 7) {
                                cell.t = 'n';
                                cell.v = numVal;
                                cell.z = '#,##0.00'; 
                            } else if (!isNaN(numVal) && C === 6) {
                                cell.t = 'n';
                                cell.v = numVal;
                                cell.z = '0.00'; 
                            } else if (!isNaN(numVal) && C === 0) {
                                cell.t = 'n';
                                cell.v = numVal;
                                cell.z = '0';
                            }
                        }
                    }
                }

                var wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, sheet, "Wastage Report");
                
                var safeFileName = 'Wastage_Damage_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
                XLSX.writeFile(wb, safeFileName);
            }
        }

        function downloadPDFDirect() {
            var element = document.getElementById('stock_table_full');
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
                                filename:     'Wastage_Damage_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_wastage', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_wastage') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>

    @include('admin.includes.date_field')

@endsection
