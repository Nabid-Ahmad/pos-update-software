
@extends('admin.master')

@section('title', ' - Purchase')

@section('content')

    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div>
                        @if(hasPermission('purchases.create'))
                            <a href="{{ route('individual.purchase') }}" class="btn btn-primary btn-sm pull-right"><i class="fa fa-plus"></i> Create Purchase</a>
                        @endif
                        <h3><i class="fa fa-th-list"></i> Purchase Information</h3>

                        <!-- filter -->
                        <div class="row">
                            <div class="col-sm-10 mx-auto">
                                <form action="" method="get">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <select name="supplier_id" class="form-control select2">
                                                <option value="">- All Suppliers -</option>
                                                @foreach($suppliers as $id => $name)
                                                    <option value="{{ $id }}" {{ request('supplier_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="input-group">
                                                <input class="form-control" name="search" placeholder="Search Invoice ID, Chalan, Date..." value="{{ request('search') }}">
                                                <div class="input-group-append">
                                                    <button style="cursor: pointer" type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>



                    <div class="tile-body table-responsive table-sm">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th width="1%">#</th>
                                    <th width="10%">Invoice ID</th>
                                    <th width="8%">Date</th>
                                    <th>Chalan No</th>
                                    <th>Supplier</th>
                                    <th>Purchase Qty</th>
                                    <th class="text-right" width="13%">Payable Amount</th>
                                    <th class="text-right" width="13%">Paid Amount</th>
                                    <th class="text-right" width="10%">Due</th>
                                    <th width="5%" class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($purchases as $key => $purchase)
                                    <tr>
                                        <td>{{ $key + $purchases->firstItem() }}</td>
                                        <td>{{ $purchase->invoiceId }}</td>
                                        <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') }}</td>
                                        <td>{{ $purchase->chalan_no }}</td>
                                        <td>{{ $purchase->supplier->name }}</td>
                                        <td>{{ $purchase->purchase_qty }}</td>
                                        <td class="text-right">{{ $total_payable = $purchase->total_payable - $purchase->invoice_discount }}</td>
                                        <td class="text-right">{{ number_format($purchase->paid, 2) }}</td>
                                        <td class="text-right">{{ number_format($total_payable - $purchase->paid, 2) }}</td>
                                        <td>

                                            <div class="btn-group btn-corner">
                                                @include('admin.includes.user-log', ['data' => $purchase])

                                                <a class="btn btn-sm btn-primary" title="Normal Print" href="{{ route('purchases.show', $purchase->id) }}">
                                                    <i class="fa fa-eye"></i>
                                                </a>

                                                @if(hasPermission('purchases.destroy'))
                                                    <a class="btn btn-sm btn-danger" title="Delete Sale" href="#"
                                                       data-toggle="modals" onclick="delete_item(`{{ route('purchases.destroy', $purchase->id) }}`)"
                                                       data-target="#DeleteModals"><i class="fa fa-trash"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @include('admin.includes.pagination', ['data' => $purchases])
                    </div>
                </div>
            </div>
        </div>
    </main>


    <!-- delete form -->
    @include('partials._delete_form')
@endsection
