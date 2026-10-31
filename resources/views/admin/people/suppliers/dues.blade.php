@extends('admin.master')
@section('title', ' - Supplier Dues')
@section('content')

    <main class="app-content">
        <div class="row">
            <div class="col-md-12">

                <div class="tile">
                    <h3 class="tile-title"> Supplier Due List </h3>

                    <!-- filter -->
                    <div class="row">
                        <div class="col-sm-8 mx-auto">
                            <form action="" method="get">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Search Supplier</span>
                                    </div>
                                    <input class="form-control form-control-md" name="search" placeholder="Search by name or phone.........." value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button style="cursor: pointer" type="submit" class="input-group-text"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <hr>
                    <div class="tile-body table-responsive">
                        <table class="table table-hover table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Sl.</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th class="text-right">Due Amount</th>
                                    <th width="15%" class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php 
                                    $totalDue = 0; 
                                    $totalAdvance = 0;
                                @endphp
                                @forelse ($suppliers as $key => $supplier)
                                    @php 
                                        if ($supplier->current_balance > 0) {
                                            $totalDue += $supplier->current_balance;
                                        } elseif ($supplier->current_balance < 0) {
                                            $totalAdvance += abs($supplier->current_balance);
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ ($suppliers->currentPage() - 1) * $suppliers->perPage() + $key + 1 }}</td>
                                        <td>{{ $supplier->supplier_code }}</td>
                                        <td>{{ $supplier->name }}</td>
                                        <td>{{ $supplier->phone }}</td>
                                        <td class="text-right">
                                            @if ($supplier->current_balance > 0)
                                                <strong class="text-danger">{{ number_format($supplier->current_balance, 2) }}</strong>
                                            @elseif ($supplier->current_balance < 0)
                                                <strong class="text-success">{{ number_format($supplier->current_balance, 2) }}</strong>
                                                <span class="badge badge-success ml-1" style="font-size: 10px;">Advance</span>
                                            @else
                                                <span class="text-muted">0.00</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('due-collections.create') }}?type=supplier&supplier_id={{ $supplier->id }}&action=payment" class="btn btn-primary btn-sm" title="Pay Supplier">
                                                <i class="fa fa-money"></i> Pay Now
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No suppliers found.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr style="background: #eee">
                                    <th colspan="4" class="text-right">Total Payable Due:</th>
                                    <th class="text-right text-danger">{{ number_format($totalDue, 2) }}</th>
                                    <th></th>
                                </tr>
                                @if($totalAdvance > 0)
                                    <tr style="background: #e8f5e9">
                                        <th colspan="4" class="text-right text-success">Total Advance (We Get):</th>
                                        <th class="text-right text-success">-{{ number_format($totalAdvance, 2) }}</th>
                                        <th></th>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>

                        @include('admin.includes.pagination', ['data' => $suppliers])
                    </div>
                </div>
            </div>
        </div>
    </main>

@endsection
