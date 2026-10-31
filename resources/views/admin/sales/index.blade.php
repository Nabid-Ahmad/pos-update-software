
@extends('admin.master')

@php
    $showSupplierColumn = \App\SoftwareSetting::where('title', 'Show Supplier In Sale List')
        ->where('options', 'yes')
        ->where('fk_company_id', auth()->user()->fk_company_id ?? companyId())
        ->exists();
@endphp

@section('title', ' - Sale')

@section('content')

    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div>
                        @if(hasPermission('sales.create'))
                        <a href="{{route('sales.create')}}" class="btn btn-primary btn-sm pull-right"><i class="fa fa-plus"></i> Add
                            Sales</a>
                        @endif
                        <h3><i class="fa fa-th-list"></i> Sale Information</h3>

                        <!-- filter -->
                        <div class="row">
                            <div class="col-sm-12">
                                <form action="" method="get" style="width: 100%">
                                    <table style="width: 100%" class="table table-borderless table-sm">
                                        <tr>
                                            <th width="20%">Start Date</th>
                                            <th width="20%">End Date</th>
                                            <th width="30%">Customer</th>
                                            <th width="20%">Invoice Id</th>
                                            <th width="10%">Action</th>
                                        </tr>
                                        <tr>
                                            <td><input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}"></td>
                                            <td><input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}"></td>
                                            <td>
                                                <select class="form-control select2" name="customer_id">
                                                    <option value="">All</option>
                                                    @foreach ($customers as $id => $customer)
                                                        <option value="{{ $id }}" {{ $id == request('customer_id') ? 'selected' : '' }}>{{ $customer }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" name="invoice_id" class="form-control" value="{{ request('invoice_id') }}"></td>
                                            <td>
                                                <button style="cursor: pointer" title="Click for Search Result" type="submit" class="btn btn-info btn-block">
                                                    <i class="fa fa-search" style="margin-right: 5px"></i> <strong>Search</strong>
                                                </button>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                        </div>
                    </div>


                    <br>


                    <div class="tile-body table-responsive table-sm">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th width="1%">#</th>
                                    <th width="10%">Invoice ID</th>
                                    <th width="8%">Date</th>
                                    <th>Customer</th>
                                    @if($showSupplierColumn)
                                        <th>Supplier</th>
                                    @endif
                                    <th class="text-center" width="10%">Seller</th>
                                    <th class="text-center" width="10%">Marketer</th>
                                    <th class="text-right" width="13%">Payable Amount</th>
                                    <th class="text-right" width="13%">Paid Amount</th>
                                    <th class="text-right" width="10%">Due</th>
                                    <th width="12%" class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($sales as $key => $sale)
                                    <tr>
                                        <td>{{ $key + $sales->firstItem() }}</td>
                                        <td>{{ $sale->invoiceId }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</td>
                                        <td>{{ optional($sale->sale_customer)->name }}</td>
                                        @if($showSupplierColumn)
                                            <td>{{ optional($sale->supplier)->name }}</td>
                                        @endif
                                        <td class="text-center">{{ optional($sale->created_user)->name }}</td>
                                        <td class="text-center">{{ optional($sale->marketers)->marketers_name }}</td>
                                        <td class="text-right">
                                            @php
                                                $sale_total = $sale->total_payable;
                                            @endphp
                                            {{ number_format($sale_total, 2) }}
                                        </td>
                                        <td class="text-right">{{ number_format($sale->paid, 2) }}</td>
                                        <td class="text-right">{{ number_format(max(0, $sale_total - $sale->paid), 2) }}</td>

                                        <td class="text-center">
                                            <div class="btn-group btn-corner">
                                                @include('admin.includes.user-log', ['data' => $sale])

                                                <a class="btn btn-sm btn-primary" title="Normal Print" target="_blank" href="{{ route('sales.show', $sale->id) }}?type=show">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a class="btn btn-sm btn-warning" title="Invoice Print" target="_blank" href="{{ route('sales.show', $sale->id) }}?print_type=invoice">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <a class="btn btn-sm btn-info" title="Pos Print" target="_blank" href="{{ route('sales.show', $sale->id) }}?print_type=pos-invoice">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <a class="btn btn-sm btn-warning" title="Invoice Print V2" target="_blank" href="{{ route('sales.show-v2', $sale->id) }}?print_type=invoice">
                                                    V2
                                                </a>
                                                {{-- @if($sale->sale_type != 'Package' && $sale->sale_details->sum('serials_count') == 0) --}}
                                                @if($sale->sale_type != 'Package')
                                                    <a class="btn btn-sm btn-success text-light" title="Edit Sale" href="{{ route('sales.edit', $sale->id) }}">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                @endif
                                                @if (optional($sale->sale_customer)->email && $isSendMail)
                                                    <a class="btn btn-sm btn-purple" title="Send Invoice To Email" href="{{ route('sales.send-message', $sale->id) }}?type=email">
                                                        <i class="fa fa-envelope"></i>
                                                    </a>
                                                @endif

                                                @if (optional($sale->sale_customer)->phone && $isSendSms)
                                                    <a class="btn btn-sm btn-ass" title="Send Invoice Message" href="{{ route('sales.send-message', $sale->id) }}?type=sms">
                                                        <i class="fa fa-telegram"></i>
                                                    </a>
                                                @endif

                                                @if(hasPermission('sales.destroy'))
                                                    <a class="btn btn-sm btn-danger" title="Delete Sale" href="#"
                                                       data-toggle="modal" onclick="deleteData({{ $sale->id }})"
                                                       data-target="#DeleteModal"><i class="fa fa-trash"></i>
                                                    </a>
                                                @endif
                                            </div>
                                            {{-- <div class="btn-group" role="group" aria-label="Button group with nested dropdown">--}}
                                            {{--     <div class="btn-group" role="group">--}}
                                            {{--         <button class="btn btn-primary btn-sm dropdown-toggle"--}}
                                            {{--                 id="btnGroupDrop3"--}}
                                            {{--                 type="button" data-toggle="dropdown" aria-haspopup="true"--}}
                                            {{--                 aria-expanded="false">Action </button>--}}
                                            {{--         <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end"--}}
                                            {{--              style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(36px, 36px, 0px);">--}}
                                            {{--             <a class="dropdown-item"--}}
                                            {{--                href="{{ url('sales/invoice', $sale->id)}}">--}}
                                            {{--                 <i class="fa fa-eye"></i> Normal Print--}}
                                            {{--             </a                                            >--}}
                                            {{--             <a class="dropdown-item" title="Pos Print"--}}
                                            {{--                href="{{ url('sales/pos-invoice', $sale->id) }}">--}}
                                            {{--                 <i class="fa fa-print"></i>  POS Print</a>--}}
                                            {{--             <a class="dropdown-item" title="Edit Sale"--}}
                                            {{--                href="{{ route('sales.edit', $sale->id) }}">--}}
                                            {{--                 <i class="fa fa-edit"></i>Edit</a                                          >--}}
                                            {{--             @if(hasPermission('sales.destroy'))--}}
                                            {{--             <a class="dropdown-item" title="Delete Sale"--}}
                                            {{--                href="#"--}}
                                            {{--                data-toggle="modal" onclick="deleteData({{ $sale->id }})"--}}
                                            {{--                data-target="#DeleteModal"><i class="fa fa-trash"></i>Delete</a>--}}
                                            {{--             @endif--}}
                                            {{--         </div>--}}
                                            {{--     </div>--}}
                                            {{-- </div>--}}
                                        </td>
                                    </tr>
                                @endforeach

                                @include('admin.includes.delete_confirm')
                            </tbody>
                        </table>

                        @include('admin.includes.pagination', ['data' => $sales])
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('footer-script')
    <script type="text/javascript">
        function deleteData(id) {
            var id = id;
            var url = '{{ route("sales.destroy", ":id") }}';
            url = url.replace(':id', id);
            $("#deleteForm").attr('action', url);
        }

        function formSubmit() {
            $("#deleteForm").submit();
        }
    </script>
@stop
