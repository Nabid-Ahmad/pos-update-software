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
                        <input class="is-add-product-cost" type="hidden" value="{{ request('update_product_cost') }}">
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
                                                        <a href="{{ url('reports/marketers-report') }}" class="btn btn-danger"><i class="fa fa-refresh"></i></a>
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

                        <form action="" method="get" style="width: 100% !important;" class="add-product-cost-area">
                            <div class="row d-print-none ">
                                {{--<input type="hidden" name="update_product_cost" value="update">
                                    <div class="col-sm-12 text-center">
                                        <span class="text-success">{{ $count }} updated</span>
                                    </div>--}}
                                <div class="col-sm-4">
                                    <p>From:</p>
                                    <input name="from" type="number" class="form-control" value="{{ request('from') }}">
                                </div>
                                <div class="col-sm-4">
                                    <p>Take:</p>
                                    <input name="count" type="number" class="form-control" value="{{ request('count') }}">
                                </div>

                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                <div class="col-sm-4">
                                    <p>Action</p>
                                    <button type="submit" class="btn btn-primary">Update Product Cost</button>
                                    <button type="button" onclick="addProductCost(0)" class="btn btn-danger">Cancel</button>
                                </div>
                        </div>
                    </form>

                        <!-- Sale List -->
                        <div class="row">
                            <div class="col-sm-11 mx-auto">
                                <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                    <thead>
                                        <tr>
                                            <td colspan="9" class="text-center py-2" style="border: none !important;">
                                                @include('partials._print_header')
                                                <h4>Marketers Report</h4>
                                                @if (request('start_date'))
                                                    <p>
                                                        Showing Sales From <b>{{ request('start_date') }}</b>
                                                        to <b>{{ request('end_date') }}</b>
                                                    </p>
                                                @endif
                                                @if(request()->filled('marketers_id'))
                                                    @php
                                                        $selectedMarketer = $marketers->firstWhere('id', request('marketers_id'));
                                                    @endphp
                                                    @if($selectedMarketer)
                                                        <p style="margin-top: 5px; font-weight: bold; font-size: 15px;">
                                                            Marketer: {{ $selectedMarketer->marketers_name }}
                                                        </p>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="5%">Sl.</th>
                                            <th width="15%">Sales Date</th>
                                            <th width="15%" style="text-align: center">Invoice ID </th>
                                            <th width="15%">Marketer name</th>
                                            <th class="text-right">Sale Price</th>
                                            <th class="text-right">percantage</th>
                                            <th class="text-right">percantage amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalPrice             = 0;
                                            $totalpercantage_amount = 0;
                                        @endphp

                                        @foreach($sales as $key => $sales_info)
                                            <tr>
                                                <td>{{ $key + 1}}</td>
                                                <td>{{ \Carbon\Carbon::parse($sales_info->sale_date)->format('d-m-Y') }}</td>
                                                <td style="text-align: center">
                                                    <a href="{{ route('sales.show', $sales_info->id) }}?print_type=invoice" target="_blank">{{ $sales_info->invoiceId }}</a>
                                                </td>
                                                <td>{{ $sales_info->marketers->marketers_name??'' }}</td>
                                                <td class="text-right">{{ number_format($sale = $sales_info->sub_total ?? 0, 2) }}</td>
                                                @php
                                                    $Commission = App\marketers_details::where('marketers_id',$sales_info->marketers_id)
                                                                           ->where('start_amount','<=', $sales_info->sub_total)
                                                                           ->where('end_amount','>=', $sales_info->sub_total)
                                                                           ->first('marketers_commission');
                                                    $sub_total            = $sales_info->sub_total ?? 0;
                                                    $marketers_commission = $Commission->marketers_commission ?? 0;
                                                    $percantage_amount    = $sale = (($sub_total * $marketers_commission)/100) ?? 0;
                                                    $invoice_discount     = $Commission->marketers_commission?? 0;
                                                @endphp
                                                <td class="text-right">{{ number_format($invoice_discount, 2) }}%</td>
                                                <td class="text-right">{{ number_format($percantage_amount , 2) }}</td>
                                            </tr>

                                            @php
                                                $totalPrice             = $totalPrice + $sale;
                                                $totalpercantage_amount = $totalpercantage_amount + $percantage_amount;
                                            @endphp
                                        @endforeach
                                    </tbody>

                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <th colspan="4"  class="text-right"><strong>Total: </strong> </th>
                                            <th class="text-right">{{ number_format($totalPrice, 2) }}</th>
                                            <th class="text-right">-</th>
                                            <th class="text-right">{{ number_format($totalpercantage_amount, 2) }}</th>
                                        </tr>
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
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Marketers Report';
                    var filterInfo = titleCell.querySelector('p') ? titleCell.querySelector('p').innerText.replace(/\s+/g, ' ').trim() : '';
                    
                    var marketerInfo = titleCell.querySelectorAll('p')[1] ? titleCell.querySelectorAll('p')[1].innerText.replace(/\s+/g, ' ').trim() : '';

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 7;

                    var newRowsHtml = `
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                        ${filterInfo ? `<tr><td colspan="${colspanVal}" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${filterInfo}</td></tr>` : ''}
                        ${marketerInfo ? `<tr><td colspan="${colspanVal}" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${marketerInfo}</td></tr>` : ''}
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
                    { wch: 15 },  // Date
                    { wch: 15 },  // Invoice ID
                    { wch: 25 },  // Marketer Name
                    { wch: 15 },  // Sale Price
                    { wch: 15 },  // Percentage
                    { wch: 20 }   // Percentage amount
                ];
                sheet['!cols'] = colWidths;

                var headerRowCount = 5;
                if(filterInfo) headerRowCount++;
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
                                // empty
                            } else if (R === headerRowCount || cell.v.toString().includes('Total')) {
                                cell.s.font.bold = true;
                                cell.s.alignment.horizontal = "center";
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if(C >= 4) cell.s.alignment.horizontal = "right";
                                if(C === 0 && cell.v.toString().includes('Total')) cell.s.alignment.horizontal = "right";
                            } else {
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C === 0 || C === 1) cell.s.alignment.horizontal = "center";
                                else if (C >= 4) cell.s.alignment.horizontal = "right";
                                else cell.s.alignment.horizontal = "center";
                            }

                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'Sl.') continue;
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal) && (C === 4 || C === 6)) {
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
                XLSX.utils.book_append_sheet(wb, sheet, "Marketers Report");
                
                var safeFileName = 'Marketers_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
                                filename:     'Marketers_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
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
    @include('admin.includes.date_field')
@endsection
