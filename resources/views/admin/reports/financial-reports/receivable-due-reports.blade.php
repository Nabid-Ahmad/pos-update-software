
@extends('admin.master')
@section('title', ' - Customer Due Report')

@push('style')
    <style type="text/css">
        @media print {
            .d-none {
                display: block !important;
            }
            .d-print {
                display: block !important;
            }
            .d-print-none {
                display: none !important;
            }
        }
        .d-print {
            display: none;
        }
        .receivable_due, th.text-right, td.text-right {
            padding-right: 15px !important;
        }
    </style>
@endpush

@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        @include('partials._alert_message')
                        
                        <!-- Filter -->
                        <div class="row d-print-none mb-3">
                            <div class="col-md-11 mx-auto">
                                <form action="{{ route('receivable.due.report') }}" method="get">
                                    <div class="row align-items-end">
                                        <div class="col-md-2">
                                            <label class="control-label font-weight-bold">Select Supplier</label>
                                            <select name="fk_supplier_id" class="form-control select2">
                                                <option value="">All Suppliers</option>
                                                @foreach($selectSuppliers as $id => $name)
                                                    <option value="{{ $id }}" {{ $id == request('fk_supplier_id') ? 'selected':'' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="control-label font-weight-bold">Select Customer</label>
                                            <select name="fk_customer_id" class="form-control select2">
                                                <option value="">All Customers</option>
                                                @foreach($selectCustomers as $id => $name)
                                                    <option value="{{ $id }}" {{ $id == request('fk_customer_id') ? 'selected':'' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="control-label font-weight-bold">Start Date</label>
                                            <input type="text" class="form-control dateField" name="from" value="{{ request()->get('from') }}" autocomplete="off" placeholder="YYYY-MM-DD">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="control-label font-weight-bold">End Date</label>
                                            <input type="text" class="form-control dateField" name="to" value="{{ request()->get('to') }}" autocomplete="off" placeholder="YYYY-MM-DD">
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-primary"><i class="fa fa-check"></i> Check</button>
                                            <a href="{{ route('receivable.due.report') }}" class="btn btn-danger ml-1"><i class="fa fa-refresh"></i></a>
                                            <button type="button" class="btn btn-success ml-1" onclick="window.print()">
                                                <i class="fa fa-print"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-11 mx-auto">
                                <table class="table table-bordered table-sm" id="stock_table" style="border: none !important;">
                                    <thead>
                                        <tr>
                                            <td colspan="9" class="text-center py-2" style="border: none !important;">
                                                @include('partials._print_header')
                                                <h5 class="text-center d-print" style="margin-top: 0 !important; margin-bottom: 2px !important;">
                                                    From {{ request('from') ?? date('F d, Y') }} {{ request('to') ? 'To '.request('to') : '' }}
                                                </h5>
                                                <h4>Customer Due Report</h4>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th width="8%">Sl.</th>
                                            <th>Customer Name</th>
                                            <th>Phone Number</th>
                                            <th>Area</th>
                                            <th>Last Sale Date</th>
                                            <th class="text-right">Opening Balance</th>
                                            <th class="text-right">Total Due Amount</th>
                                            <th class="text-center d-print-none" width="15%">Action</th>
                                        </tr>
                                    </thead>

                                    @php
                                        $total = 0;
                                        $total_ob = 0;
                                        $rowNumber = 0;
                                    @endphp

                                    @isset($customerBalances)
                                        <tbody>
                                        @foreach ($customers as $key => $customer)
                                            @if ($customer->current_balance != 0)
                                                @php
                                                    $rowNumber++;
                                                @endphp
                                                <tr>
                                                    <td>{{ $rowNumber }}</td>
                                                    <td>{{ $customer->name }}</td>
                                                    <td>{{ $customer->phone }}</td>
                                                    <td>{{ optional($customer->customer_category)->name }}</td>
                                                    <td>{{ $customer->last_sale_date ? \Carbon\Carbon::parse($customer->last_sale_date)->format('Y-m-d') : 'Opening Balance' }}</td>
                                                    <td class="text-right">{{ number_format($customer->customer_previous_due ?? 0, 2) }}</td>
                                                    <td class="text-right receivable_due">{{ number_format($customer->current_balance ?? 0, 2) }}</td>
                                                    <td class="text-center d-print-none">
                                                        <button type="button" 
                                                                class="btn btn-sm btn-primary pay-due-btn" 
                                                                data-id="{{ $customer->id }}" 
                                                                data-name="{{ $customer->name }}" 
                                                                data-due="{{ $customer->current_balance }}">
                                                            <i class="fa fa-money"></i> Pay Due
                                                        </button>
                                                    </td>
                                                </tr>
                                                @php
                                                    $total += $customer->current_balance;
                                                    $total_ob += $customer->customer_previous_due ?? 0;
                                                @endphp
                                            @endif
                                        @endforeach
                                        </tbody>

                                        <tfoot>
                                        <tr style="background-color: #f8f9fa;">
                                            <th colspan="5"><strong>Total:</strong></th>
                                            <th class="text-right">{{ number_format($total_ob ?? 0, 2) }}</th>
                                            <th class="text-right total_due">{{ number_format($total, 2) }}</th>
                                            <th class="d-print-none"></th>
                                        </tr>
                                        </tfoot>
                                    @endisset

                                </table>

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
            </div>
        </div>
    </main>

    <!-- Pay Due Modal -->
    <div class="modal fade" id="payDueModal" role="dialog" aria-labelledby="payDueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('due-collections.store') }}?type=customer" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="modal_customer_id">
                    <input type="hidden" name="previous_balance" id="modal_previous_balance">
                    <input type="hidden" name="advanced_payment" id="modal_advanced_payment">
                    
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="payDueModalLabel"><i class="fa fa-money"></i> Pay Customer Due</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Customer Name</label>
                                    <input type="text" id="modal_customer_name" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Due Amount</label>
                                    <input type="text" id="modal_due_amount" class="form-control font-weight-bold" readonly style="font-size: 18px; color: #d9534f;">
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Date <span class="text-danger">*</span></label>
                                    <input type="text" name="date" value="{{ date('Y-m-d') }}" required class="form-control modal-dateField" placeholder="Date">
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method_id" class="form-control select2-modal" style="width:100%;">
                                        @foreach($paymentMethods as $id => $name)
                                            <option value="{{ $id }}">{{ ucwords($name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Account <span class="text-danger">*</span></label>
                                    <select name="account_id" class="form-control select2-modal select-account-modal" style="width:100%;">
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}" data-total-amount="{{ $account->total_amount }}">{{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Discount Amount</label>
                                    <input type="number" step="any" name="discount" class="form-control modal-discount" autocomplete="off" placeholder="0.00" style="font-size: 17px;">
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Paid Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="any" name="amount" class="form-control modal-paid" required autocomplete="off" placeholder="0.00" style="font-size: 17px;">
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Remaining Due</label>
                                    <input type="text" name="payable_dueAmount" id="modal_remaining_due" class="form-control" readonly style="font-size: 17px;">
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Note</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Write any note here..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-check-circle"></i> Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/admin/js/plugins/select2.min.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2').select2();
            $('.select2-modal').select2({
                dropdownParent: $('#payDueModal')
            });
            
            // Initialize Datepicker for modal
            $('.modal-dateField').datepicker({
                format: "yyyy-mm-dd",
                autoclose: true,
                todayHighlight: true
            });
        });

        $(document).on('click', '.pay-due-btn', function() {
            let customerId = $(this).data('id');
            let customerName = $(this).data('name');
            let dueAmount = parseFloat($(this).data('due'));

            $('#modal_customer_id').val(customerId);
            $('#modal_customer_name').val(customerName);
            $('#modal_due_amount').val(dueAmount.toFixed(2));
            
            let previous_balance = dueAmount >= 0 ? dueAmount : 0;
            let advanced_payment = dueAmount < 0 ? Math.abs(dueAmount) : 0;
            
            $('#modal_previous_balance').val(previous_balance);
            $('#modal_advanced_payment').val(advanced_payment);
            
            $('.modal-paid').val('');
            $('.modal-discount').val('');
            $('#modal_remaining_due').val(dueAmount.toFixed(2));

            // Show modal
            $('#payDueModal').modal('show');
        });

        // Calculate remaining due
        $(document).on('keyup change', '.modal-paid, .modal-discount', function() {
            let dueAmount = parseFloat($('#modal_due_amount').val()) || 0;
            let paid = parseFloat($('.modal-paid').val()) || 0;
            let discount = parseFloat($('.modal-discount').val()) || 0;

            let remaining = dueAmount - paid - discount;
            $('#modal_remaining_due').val(remaining.toFixed(2));
        });

        function downloadExcelDirect() {
            var originalTable = document.getElementById('stock_table');
            if (!originalTable) return;
            
            // Dynamically load xlsx-js-style instead of standard sheetjs
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

                // Remove all elements with the 'd-print-none' class (like action headers, pay due buttons, etc.)
                var dPrintNoneElements = clonedTable.querySelectorAll('.d-print-none');
                dPrintNoneElements.forEach(function(el) {
                    el.parentNode.removeChild(el);
                });

                // Adjust the header colspan and split the nested header into clean separate rows
                var titleRow = clonedTable.querySelector('thead tr');
                if (titleRow && titleRow.querySelector('td[colspan], th[colspan]')) {
                    var titleCell = titleRow.querySelector('td[colspan], th[colspan]');
                    
                    var companyName = titleCell.querySelector('h3') ? titleCell.querySelector('h3').innerText : '';
                    var address = titleCell.querySelectorAll('h5')[0] ? titleCell.querySelectorAll('h5')[0].innerText : '';
                    var contact = titleCell.querySelectorAll('h5')[1] ? titleCell.querySelectorAll('h5')[1].innerText : '';
                    var dateRange = titleCell.querySelectorAll('h5')[2] ? titleCell.querySelectorAll('h5')[2].innerText : '';
                    var reportTitle = titleCell.querySelector('h4') ? titleCell.querySelector('h4').innerText : 'Customer Due Report';

                    // Dynamically calculate exact number of columns to merge so headers center perfectly over the data
                    var dataHeaders = clonedTable.querySelectorAll('thead tr:last-child th');
                    var colspanVal = dataHeaders.length > 0 ? dataHeaders.length : 4;

                    var newRowsHtml = `
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 16pt; font-weight: bold; border: none;">${companyName}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${address}</td></tr>
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; color: #555; border: none;">${contact}</td></tr>
                        ${dateRange ? `<tr><td colspan="${colspanVal}" style="text-align: center; font-size: 10pt; font-weight: bold; border: none;">${dateRange}</td></tr>` : ''}
                        <tr><td colspan="${colspanVal}" style="text-align: center; font-size: 14pt; font-weight: bold; border: none; height: 30px; vertical-align: middle;">${reportTitle}</td></tr>
                        <tr><td colspan="${colspanVal}" style="border: none; height: 10px;"></td></tr>
                    `;
                    
                    var tempDiv = document.createElement('tbody');
                    tempDiv.innerHTML = newRowsHtml;
                    var parent = titleRow.parentNode;
                    
                    while (tempDiv.firstChild) {
                        parent.insertBefore(tempDiv.firstChild, titleRow);
                    }
                    parent.removeChild(titleRow);
                }

                // Convert table to SheetJS sheet
                var sheet = XLSX.utils.table_to_sheet(clonedTable, { raw: false });

                // Define column widths dynamically
                var range = XLSX.utils.decode_range(sheet['!ref']);
                var numCols = range.e.c - range.s.c + 1;
                var colWidths = [
                    { wch: 8 },   // Sl.
                    { wch: 30 },  // Customer Name
                    { wch: 18 },  // Phone/Last Sale Date
                    { wch: 18 }   // Due Amount
                ];
                sheet['!cols'] = colWidths;

                // Loop through all data cells in the sheet and format number values & styles correctly
                var headerRowCount = dateRange ? 6 : 5; // Rows before the actual table header

                for (var R = range.s.r; R <= range.e.r; ++R) {
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        var cell_ref = XLSX.utils.encode_cell({c: C, r: R});
                        var cell = sheet[cell_ref];
                        if (cell && cell.v !== undefined) {
                            
                            // Initialize styles
                            cell.s = {
                                font: { name: 'Arial', sz: 11 },
                                alignment: { vertical: "center" }
                            };

                            // Apply styles based on row type
                            if (R < headerRowCount - 1) {
                                // Top Company Headers
                                cell.s.alignment.horizontal = "center";
                                cell.s.font.bold = true;
                                if (R === 0) cell.s.font.sz = 14; // Company Name larger
                            } 
                            else if (R === headerRowCount - 1) {
                                // Empty row before table
                            }
                            else if (R === headerRowCount) {
                                // Table Headers (Sl, Customer Name, etc)
                                cell.s.font.bold = true;
                                cell.s.alignment.horizontal = "center";
                                cell.s.border = {
                                    top: {style: "thin"}, bottom: {style: "thin"},
                                    left: {style: "thin"}, right: {style: "thin"}
                                };
                            }
                            else {
                                // Table Data Rows
                                cell.s.border = {
                                    top: {style: "thin"}, bottom: {style: "thin"},
                                    left: {style: "thin"}, right: {style: "thin"}
                                };
                                
                                if (C === 0) {
                                    // Sl no to the left
                                    cell.s.alignment.horizontal = "left";
                                } else if (C === 3) {
                                    // Amounts to the right
                                    cell.s.alignment.horizontal = "right";
                                } else {
                                    cell.s.alignment.horizontal = "center";
                                }
                            }

                            // Value parsing for numbers
                            var valStr = (cell.v + '').replace(/,/g, '').trim();
                            if (valStr === '' || isNaN(valStr) || valStr === 'Sl.' || valStr === 'Grand Total:' || valStr === 'Total this page:' || valStr === 'Total:') {
                                continue;
                            }
                            
                            var numVal = parseFloat(valStr);
                            if (!isNaN(numVal)) {
                                cell.t = 'n';
                                cell.v = numVal;

                                if (C === 0) cell.z = '0'; // Sl.
                                if (C === 3) cell.z = '#,##0.00'; // Due Amount
                            }
                        }
                    }
                }

                // Create workbook and write file
                var wb = XLSX.utils.book_new();
                var sheetName = reportTitle.length > 30 ? reportTitle.substring(0, 30) : reportTitle;
                XLSX.utils.book_append_sheet(wb, sheet, sheetName);
                
                var safeFileName = reportTitle.replace(/[^a-zA-Z0-9]/g, '_') + '_' + new Date().toISOString().slice(0,10) + '.xlsx';
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
            iframe.style.width = '720px';
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
                        .text-center, .receivable_due { text-align: center; }
                        .text-right { text-align: right; }
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
                                filename:     'Customer_Due_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                                image:        { type: 'jpeg', quality: 0.98 },
                                html2canvas:  { scale: 2, useCORS: true, logging: false },
                                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
                            };
                            
                            html2pdf().set(opt).from(document.getElementById('pdf-content')).save().then(function() {
                                window.parent.postMessage('pdf_done_due', '*');
                            });
                        };
                    <\/script>
                </body>
                </html>
            `);
            doc.close();

            var listener = function(e) {
                if(e.data === 'pdf_done_due') {
                    document.body.removeChild(iframe);
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
        }
    </script>
@endsection
