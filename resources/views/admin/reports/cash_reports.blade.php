@php
    function getModelValue($model) {
        $models = ['App\Sale', 'App\Purchase', 'App\FundTransfer', 'App\VoucherChartPayment', 'App\AccountReview', 'App\Models\RandomReturn'];
        $values = ['Sale', 'Purchase', 'Fund Transfer', 'Voucher', 'Payment', 'Product Return'];

        $index = array_search($model, $models);
        if($index !== false) {
            return $values[$index];
        } else {
            return $model;
        }
    }

    function getTransactionDetails($transaction) {
        $type = $transaction->transactionable_type;
        $amount = $transaction->amount;
        $ref = '';
        $desc = '';
        $link = '';
        $party = '-';

        if ($type == 'App\Sale') {
            $sale = $transaction->transactionable;
            if ($sale) {
                $ref = '#S-' . $sale->sale_invoice_id;
                $link = route('sales.show', $sale->id);
                $party = optional($sale->customer)->name ?? '-';
                if ($amount > 0) {
                    $desc = 'Received Payment for Sale';
                } else {
                    $desc = 'Paid Delivery Charge for Sale';
                }
            } else {
                $desc = 'Sale Transaction';
            }
        } elseif ($type == 'App\Purchase') {
            $purchase = $transaction->transactionable;
            if ($purchase) {
                $ref = $purchase->invoiceId;
                $link = route('purchases.show', $purchase->id);
                $party = optional($purchase->supplier)->name ?? '-';
                $desc = 'Paid for Purchase';
            } else {
                $desc = 'Purchase Transaction';
            }
        } elseif ($type == 'App\VoucherChartPayment') {
            $vcp = $transaction->transactionable;
            if ($vcp && $vcp->voucher) {
                $ref = $vcp->voucher->voucher_reference ?? ('#V-' . $vcp->voucher->id);
                $link = route('payments.show', ['payment' => $vcp->voucher->id, 'type' => $vcp->voucher->voucher_type]);
                $party = optional($vcp->voucher->party)->name ?? '-';
                $desc = 'Voucher Payment';
            } else {
                $desc = 'Voucher Payment';
            }
        } elseif ($type == 'App\AccountReview') {
            $ar = $transaction->transactionable;
            if ($ar) {
                $ref = $ar->invoice_id;
                $link = route('due-collections.show', $ar->id);
                $party = optional($ar->consumer)->name ?? '-';
                $desc = 'Account Payment';
            } else {
                $desc = 'Payment';
            }
        } elseif ($type == 'App\FundTransfer') {
            $desc = 'Fund Transfer';
        } else {
            $desc = str_replace('App\\', ' ', getModelValue($type));
        }

        return [
            'ref' => $ref,
            'desc' => $desc,
            'link' => $link,
            'party' => $party
        ];
    }
@endphp
@extends('admin.master')
@section('title', ' - Cash Flow Report')

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
        .card-summary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
        }
    </style>
@endpush
@section('content')

    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="no-print d-flex align-items-center justify-content-between flex-wrap pb-3 mb-3 border-bottom">
                        <h3 class="tile-title mb-0 text-dark font-weight-bold" style="font-size: 1.5rem; flex-shrink: 0;">
                            <span style="border-left: 4px solid #007bff; padding-left: 10px;">Cash Report</span>
                        </h3>

                        <form action="" method="get" class="d-flex align-items-center justify-content-center flex-wrap" style="gap: 12px; margin-left: auto; margin-right: auto; flex-grow: 1;">
                            <div class="input-group input-group-sm" style="width: 140px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0 text-muted" style="padding: 0.25rem 0.5rem;"><i class="fa fa-calendar"></i></span>
                                </div>
                                <input type="text" name="from" value="{{ request('from') ?? date('Y-m-d') }}" placeholder="From" class="form-control form-control-sm border-left-0 dateField" style="border-radius: 0 4px 4px 0; height: 31px;">
                            </div>

                            <span class="text-muted font-weight-bold" style="font-size: 1.1rem; line-height: 1;">&mdash;</span>

                            <div class="input-group input-group-sm" style="width: 140px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0 text-muted" style="padding: 0.25rem 0.5rem;"><i class="fa fa-calendar"></i></span>
                                </div>
                                <input type="text" name="to" value="{{ request('to') ?? date('Y-m-d') }}" placeholder="To" class="form-control form-control-sm border-left-0 dateField" style="border-radius: 0 4px 4px 0; height: 31px;">
                            </div>

                            <div class="input-group input-group-sm" style="width: 220px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0 text-muted" style="padding: 0.25rem 0.5rem;"><i class="fa fa-university"></i></span>
                                </div>
                                <select name="account_id" onchange="this.form.submit()" class="form-control form-control-sm border-left-0" style="border-radius: 0 4px 4px 0; height: 31px;">
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $id => $name)
                                        <option value="{{ $id }}" {{ request('account_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm px-3 d-flex align-items-center justify-content-center" style="border-radius: 4px; font-weight: 500; height: 31px; gap: 6px;">
                                <i class="fa fa-search"></i> <span>Filter</span>
                            </button>
                        </form>
                    </div>

                    <div class="tile-body">
                        <!-- Glassmorphic Financial Summary Cards -->
                        <div class="row no-print mb-4 mt-2">
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm card-summary" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-radius: 12px; transition: all 0.3s ease;">
                                    <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6 class="text-success font-weight-bold mb-1" style="font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Inflow (Cr)</h6>
                                            <h3 class="font-weight-bold text-dark mb-0">{{ number_format($totalInflow, 2) }}</h3>
                                        </div>
                                        <div class="rounded-circle p-2" style="background: rgba(40, 167, 69, 0.15);">
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
        </div>
    </main>
@endsection

@section('footer-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        $(() => {
            $('.account_id').val({{request('account_id')}})
        });

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
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Cash Flow Report';
                    var dateInfo = titleCell.querySelector('p') ? titleCell.querySelector('p').innerText.replace(/\s+/g, ' ').trim() : '';

                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 8;

                    var newRowsHtml = `
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                        ${dateInfo ? `<tr><td colspan="${colspanVal}" style="text-align: center; font-size: 11pt; font-weight: bold; border: none;">${dateInfo}</td></tr>` : ''}
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
                    { wch: 15 },  // Invoice
                    { wch: 18 },  // Account
                    { wch: 30 },  // Description
                    { wch: 15 },  // Dr
                    { wch: 15 },  // Cr
                    { wch: 18 }   // Balance
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
                                // empty
                            } else if (R === headerRowCount || R === headerRowCount + 1) { // Table headers / Opening balance
                                cell.s.font.bold = true;
                                cell.s.alignment.horizontal = "center";
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if(C >= 5) cell.s.alignment.horizontal = "right";
                            } else {
                                cell.s.border = { top: {style: "thin"}, bottom: {style: "thin"}, left: {style: "thin"}, right: {style: "thin"} };
                                if (C === 0 || C === 1 || C === 2) cell.s.alignment.horizontal = "center";
                                else if (C >= 5) cell.s.alignment.horizontal = "right";
                                else cell.s.alignment.horizontal = "left";
                            }

                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'SL' || valStr.includes('Balance') || valStr.includes('Opening')) continue;
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal) && C >= 5) {
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
                XLSX.utils.book_append_sheet(wb, sheet, "Cash Flow");
                
                var safeFileName = 'Cash_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
                                filename:     'Cash_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' },
                                pagebreak:    { mode: ['avoid-all', 'css'] }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_cash', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_cash') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
@stop
