@extends('admin.master')

@section('title', ' - Brand Wise Summary Report')
@push('style')
    <style type="text/css">
        @media print {
            .d-none {
                display: block !important;
            }
            .d-print {
                display: block !important;
            }
            .no-print {
                display: none !important;
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
                            <div class="col-md-12">
                                <div class="table-responsive">

                                    <form action="" method="get">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td width="30%">
                                                    <label>Brand</label>
                                                    <select name="fk_brand_id" class="form-control select2">
                                                        <option value="">All Brands</option>
                                                        @foreach ($brands as $id => $name)
                                                            <option value="{{ $id }}" {{ $id == request()->fk_brand_id ? 'selected':'' }}>{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="10%" class="no-print">
                                                    <label class="control-label">Start Date</label>
                                                    <input type="text" class="form-control dateField" name="start_date"
                                                           value="{{ request()->get('start_date') ?? date('Y-m-d') }}"
                                                           autocomplete="off">
                                                </td>

                                                <td width="10%" class="no-print">
                                                    <label class="control-label">End Date</label>
                                                    <input type="text" class="form-control dateField" name="end_date"
                                                           value="{{ request()->get('end_date') ?? date('Y-m-d') }}"
                                                           autocomplete="off">
                                                </td>


                                                 <td width="30%" class="no-print text-right">
                                                     <div class="form-group" style="margin-top: 26px;">
                                                         <button class="btn btn-primary"><i class="fa fa-check"></i> Check</button>
                                                         <a href="{{ route('brand.wise.summary.report') }}" class="btn btn-danger"><i class="fa fa-refresh"></i></a>
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
                                            <td colspan="5" class="text-center py-2" style="border: none !important;">
                                                @include('partials._print_header')
                                                <h4>Brand Wise Summary Report</h4>
                                                @if (request('start_date'))
                                                    <p>
                                                        Showing sales From <b>{{ request('start_date') }}</b>
                                                        to <b>{{ request('end_date') }}</b>
                                                    </p>
                                                @endif
                                                @if(request()->filled('fk_brand_id') && isset($brands[request('fk_brand_id')]))
                                                    <p style="margin-top: 5px; font-weight: bold; font-size: 15px;">
                                                        Brand: {{ $brands[request('fk_brand_id')] }}
                                                    </p>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="5%">Sl.</th>
                                            <th>Brand Name</th>
                                            <th class="text-right">Total Sale</th>
                                            <th class="text-right">Total Collection</th>
                                            <th class="text-right">Total Due</th>
                                        </tr>
                                    </thead>

                                     <tbody>
                                         @isset($brandSummaries)
                                             @php
                                                 $totalSale = 0;
                                                 $totalCollection = 0;
                                                 $totalDue = 0;
                                                 $sl = 1;
                                             @endphp

                                             @foreach($brandSummaries as $brandId => $summary)
                                                 <tr>
                                                     <td>{{ $sl++ }}</td>
                                                     <td>{{ $summary['brand_name'] }}</td>
                                                     @php
                                                         $totalSale += $summary['total_sale'];
                                                         $totalCollection += $summary['total_collection'];
                                                         $totalDue += $summary['total_due'];
                                                     @endphp
                                                     <td class="text-right">{{ number_format($summary['total_sale'], 2) }}</td>
                                                     <td class="text-right">{{ number_format($summary['total_collection'], 2) }}</td>
                                                     <td class="text-right">{{ number_format($summary['total_due'], 2) }}</td>
                                                 </tr>
                                             @endforeach
                                             <tr>
                                                 <td colspan="2" class="text-right"><strong>Total:</strong></td>
                                                 <td class="text-right"><strong>{{ number_format($totalSale, 2) }}</strong></td>
                                                 <td class="text-right"><strong>{{ number_format($totalCollection, 2) }}</strong></td>
                                                 <td class="text-right"><strong>{{ number_format($totalDue, 2) }}</strong></td>
                                             </tr>
                                         @endisset
                                     </tbody>
                                </table>

                                 <!-- Modern PDF & Excel Download Buttons at Bottom Left -->
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
                                     <a href="javascript:void(0)" onclick="window.print()" class="download-icon-btn" title="Download PDF">
                                         @include('admin.reports.includes.pdf_icon')
                                     </a>
                                     
                                     <!-- Excel Icon using external Excel export function -->
                                     <a href="javascript:void(0)" onclick="exportTableToExcel('stock_table', 'Brand_Wise_Summary_Report')" class="download-icon-btn" title="Download Excel">
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
@endsection

@section('js')
    <script>
        function exportTableToExcel(tableID, filename = ''){
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
            var tableSelect = document.getElementById(tableID);
            
            // Clone table to remove non-printable elements
            var clonedTable = tableSelect.cloneNode(true);
            var noPrintElements = clonedTable.querySelectorAll('.no-print');
            noPrintElements.forEach(function(elem) {
                elem.parentNode.removeChild(elem);
            });
            
            var tableHTML = clonedTable.outerHTML.replace(/ /g, '%20');
            filename = filename?filename+'.xls':'excel_data.xls';
            downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            if(navigator.msSaveOrOpenBlob){
                var blob = new Blob(['\ufeff', tableHTML], {
                    type: dataType
                });
                navigator.msSaveOrOpenBlob( blob, filename);
            }else{
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
                downloadLink.download = filename;
                downloadLink.click();
            }
        }
    </script>
@endsection
