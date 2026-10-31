@extends('admin.master')
@section('title', ' - Marketers Report')
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
@php
    $count=0;
@endphp
@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <!-- Filter -->
                        <div class="row d-print-none filter-area">
                            <div class="col-md-11 mx-auto">
                                <div class="table-responsive">
                                    <form action="" method="get">
                                        <input type="hidden" name="update_product_cost" value="">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td width="20%">
                                                    <label>Marketer</label>
                                                    <select name="marketers_id" class="form-control select2">
                                                        <option value="">All</option>
                                                        @foreach ($marketers as $key => $marketer)
                                                            <option value="{{ $marketer->id }}" {{ $marketer->id == request()->marketers_id ? 'selected':'' }}>
                                                                {{ $marketer->marketers_name??'' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="20%" class="no-print">
                                                    <label class="control-label">Start Date</label>
                                                    <input type="text" class="form-control dateField" name="start_date" value="{{ request()->get('start_date') ?? date('Y-m-d') }}" autocomplete="off">
                                                </td>
                                                <td width="20%" class="no-print">
                                                    <label class="control-label">End Date</label>
                                                    <input type="text" class="form-control dateField" name="end_date" value="{{ request()->get('end_date') ?? date('Y-m-d') }}" autocomplete="off">
                                                </td>
                                                <td width="30%" class="no-print text-right">
                                                    <div class="form-group" style="margin-top: 26px;">
                                                        <button class="btn btn-primary"><i class="fa fa-check"></i> Check </button>
                                                        <a href="{{ url('reports/marketers-ledger') }}" class="btn btn-danger"><i class="fa fa-refresh"></i></a>
                                                        @if (auth()->user()->id == 1)
                                                            <button type="button" class="btn btn-warning" onclick="addProductCost(1)">
                                                                <i class="fa fa-gear"></i> Add Product Cost
                                                            </button>
                                                        @endif

                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Marketers List -->
                        <div class="row">
                            <div class="col-sm-11 mx-auto">
                                <div id="report-wrapper">
                                <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                    <thead>
                                        <tr>
                                            <td colspan="9" class="text-center py-2" style="border: none !important;">
                                                @include('partials._print_header')
                                                <h4>Marketers Ledger Report</h4>
                                                @if(request()->filled('start_date') && request()->filled('end_date'))
                                                    <p class="text-muted" style="font-size: 14px; margin-bottom: 5px;">
                                                        Showing Ledger From <strong>{{ request('start_date') }}</strong> to <strong>{{ request('end_date') }}</strong>
                                                    </p>
                                                @endif
                                                @if(request()->filled('marketers_id'))
                                                    @php
                                                        $selectedMarketer = $marketers->firstWhere('id', request('marketers_id'));
                                                    @endphp
                                                    @if($selectedMarketer)
                                                        <h5 style="margin-top: 5px; font-weight: bold; color: #333;">Marketer: {{ $selectedMarketer->marketers_name }}</h5>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="5%">Sl.</th>
                                            <th width="15%" class="text-center">Amount&nbsp;Given Date</th>
                                            <th >Marketer&nbsp;name</th>
                                            <th width="15%" class="text-center">Amount</th>
                                            <th width="15%" class="text-center">Amount&nbsp;Update Date</th>
                                            <th width="5%" class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($marketersLedgerDatas as $key => $marketersLedgerData)
                                            <tr>
                                                <td>{{ $key + 1}}</td>
                                                <td class="text-center">{{ $marketersLedgerData->created_at->format('Y-m-d') }}</td>
                                                <td>{{ $marketersLedgerData->marketers->marketers_name??'' }}</td>
                                                <td class="text-center">{{ number_format($marketersLedgerData->amount ?? 0, 2) }}</td>
                                                <td class="text-center">{{ $marketersLedgerData->updated_at->format('Y-m-d') }}</td>
                                                <td class="text-right">
                                                    <a class="btn btn-primary btn-sm" title="Edit"
                                                        href="{{ route('marketers.ledger.edit',$marketersLedgerData->id) }}">
                                                    <i class="fa fa-edit"></i>
                                                    </a>
                                                </td>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="no-print d-print-none">
                                        <tr>
                                            <td colspan="110" class="text-right">{{ $marketersLedgerDatas->links() }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
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


    <script type="text/javascript">

        $('.filter-area').hide()
        $('.add-product-cost-area').hide()

        function addProductCost(status)
        {
            if (status == 1) {
                $('.filter-area').hide()
                $('.add-product-cost-area').show()
            } else {
                $('.filter-area').show()
                $('.add-product-cost-area').hide()
            }
        }

        $(document).ready(function () {
            if($('.is-add-product-cost').val() == 'update') {
                addProductCost(1)
            } else {
                addProductCost(0)
            }
        })
    </script>
    @include('admin.includes.date_field')

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script type="text/javascript">
        function downloadExcelDirect() {
            var table = document.getElementById('stock_table');
            if (!table) return;

            if (typeof XLSX === 'undefined') {
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js';
                script.onload = performExport;
                document.head.appendChild(script);
            } else {
                performExport();
            }

            function performExport() {
                var titleCell = document.querySelector('#report-wrapper');
                var companyName = titleCell && titleCell.querySelector('h2') ? titleCell.querySelector('h2').innerText : '';
                var address = titleCell && titleCell.querySelectorAll('p')[0] ? titleCell.querySelectorAll('p')[0].innerText : '';
                var contact = titleCell && titleCell.querySelectorAll('p')[1] ? titleCell.querySelectorAll('p')[1].innerText : '';
                var reportTitle = 'Marketers Ledger Report';
                
                var dateInfo = '';
                var dateP = titleCell ? titleCell.querySelector('.text-muted') : null;
                if (dateP) dateInfo = dateP.innerText.replace(/\s+/g, ' ').trim();
                
                var marketerInfo = '';
                var h5 = titleCell ? titleCell.querySelector('h5') : null;
                if (h5) marketerInfo = h5.innerText.trim();

                var rows = Array.from(table.querySelectorAll('tr'));
                var virtualTable = document.createElement('table');
                var tbody = document.createElement('tbody');
                virtualTable.appendChild(tbody);

                var newRowsHtml = `
                    <tr><td colspan="5" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                    <tr><td colspan="5" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                    <tr><td colspan="5" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                    <tr><td colspan="5" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                    ${dateInfo ? `<tr><td colspan="5" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${dateInfo}</td></tr>` : ''}
                    ${marketerInfo ? `<tr><td colspan="5" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${marketerInfo}</td></tr>` : ''}
                    <tr><td colspan="5" style="border: none; height: 10px;"></td></tr>
                `;
                tbody.innerHTML = newRowsHtml;

                for (var i = 0; i < rows.length; i++) {
                    // Skip footer rows since they only have pagination
                    if (rows[i].closest('tfoot')) continue;
                    
                    var tr = document.createElement('tr');
                    var cells = Array.from(rows[i].cells);
                    
                    // Skip header row containing the complex HTML (company info, etc)
                    if (cells.length === 1 && cells[0].getAttribute('colspan') === '9') continue;

                    cells.forEach(function(c, index) {
                        // Skip the action column (index 5)
                        if (index === 5) return;
                        
                        var newCell = c.cloneNode(true);
                        // Convert colspan 9 to 5 if any
                        if (newCell.getAttribute('colspan') === '9') {
                            newCell.setAttribute('colspan', '5');
                        }
                        tr.appendChild(newCell);
                    });
                    
                    if (tr.childNodes.length > 0) {
                        tbody.appendChild(tr);
                    }
                }

                var sheet = XLSX.utils.table_to_sheet(virtualTable, { raw: false });
                var range = XLSX.utils.decode_range(sheet['ref'] || sheet['!ref']);
                
                var colWidths = [
                    { wch: 6 },   // SL
                    { wch: 20 },  // Date
                    { wch: 25 },  // Name
                    { wch: 15 },  // Amount
                    { wch: 20 }   // Update Date
                ];
                sheet['!cols'] = colWidths;

                var headerRowCount = 5;
                if(dateInfo) headerRowCount++;
                if(marketerInfo) headerRowCount++;

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
                            } else {
                                // Table data styling
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                
                                var valStr = (cell.v + '').replace(/,/g, '').trim();

                                if (valStr === 'Sl.' || valStr === 'Amount Given Date' || valStr === 'Marketer name' || valStr === 'Amount' || valStr === 'Amount Update Date') {
                                    cell.s.font.bold = true;
                                    cell.s.alignment.horizontal = "center";
                                } else {
                                    if (C === 0 || C === 1 || C === 4) cell.s.alignment.horizontal = "center";
                                    else if (C === 3) cell.s.alignment.horizontal = "right";
                                    else cell.s.alignment.horizontal = "left";
                                }

                                if (valStr !== '' && !isNaN(valStr) && valStr !== 'Sl.') {
                                    var numVal = parseFloat(valStr);
                                    if (!isNaN(numVal)) {
                                        if (C === 3) { // Amount
                                            cell.t = 'n';
                                            cell.v = numVal;
                                            cell.z = '#,##0.00';
                                        } else if (C === 0) { // Sl
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
                XLSX.utils.book_append_sheet(wb, sheet, "Marketers Ledger");
                
                var safeFileName = 'Marketers_Ledger_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
                    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
                    <style>
                        body { font-family: Arial, sans-serif; font-size: 12px; background: #fff; margin: 0; padding: 20px; color: #000; }
                        table { width: 100%; border-collapse: collapse; background: #fff; margin-bottom: 20px; }
                        th, td { border: 1px solid #dee2e6 !important; padding: 8px; vertical-align: middle; color: #000; }
                        th { background-color: #f8f9fa; font-weight: bold; }
                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        .text-left { text-align: left; }
                        h2, h3, h4, h5, p { text-align: center; margin: 5px 0; color: #000; }
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
                            // Hide the action column in PDF
                            var table = document.getElementById('stock_table');
                            if(table) {
                                var rows = table.querySelectorAll('tr');
                                rows.forEach(function(row) {
                                    if(row.cells.length > 5) {
                                        row.cells[5].style.display = 'none';
                                    }
                                });
                            }
                            
                            var opt = {
                                margin:       [0.5, 0.5, 0.5, 0.5],
                                filename:     'Marketers_Ledger_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_marketers', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_marketers') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
@endsection
@endsection
