@extends('admin.master')
@section('content')

    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div style="margin-bottom: 20px">
                        @if(hasPermission('sale.products.return'))
                            <a href="{{ route('sale-returns.create') }}" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add Return/Exchange</a>
                        @endif
                        <h3><i class="fa fa-exchange"> </i> Sale Exchange Info</h3>
                    </div>

                    <div class="tile-body table-responsive table-sm">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th width="1%">#</th>
                                    <th>Invoice ID</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Reference</th>
                                    <th class="text-right">Return Amount</th>
                                    <th class="text-right">Exchange Amount</th>
                                    <th class="text-right">Net Adjustment</th>
                                    <th width="5%" class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php $sl = 1 @endphp

                                @foreach ($saleExchanges as $exchange)
                                    <tr>
                                        <td>{{ $sl++ }}</td>
                                        <td>{{ $exchange->invoice_no }}</td>
                                        <td>{{ \Carbon\Carbon::parse($exchange->date)->format('d-m-Y') }}</td>
                                        <td>{{ optional($exchange->customer)->name }}</td>
                                        <td>{{ $exchange->reference }}</td>
                                        <td class="text-right">{{ number_format($exchange->amount, 2) }}</td>
                                        <td class="text-right text-info">{{ number_format($exchange->exchange_amount, 2) }}</td>
                                        <td class="text-right">
                                            @php $net = $exchange->exchange_amount - $exchange->amount @endphp
                                            <span class="{{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($net, 2) }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <div class="btn-group btn-corner">
                                                @include('admin.includes.user-log', ['data' => $exchange])

                                                <a class="btn btn-sm btn-primary" title="View Details" href="{{ route('sale-returns.show', $exchange->id) }}">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a class="btn btn-sm btn-danger" title="Delete" href="#"
                                                   data-toggle="modal" onclick="deleteData({{ $exchange->id }})"
                                                   data-target="#DeleteModal"><i class="fa fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                @include('admin.includes.delete_confirm')
                            </tbody>
                        </table>
                        @include('admin.includes.pagination', ['data' => $saleExchanges])
                    </div>
                </div>
            </div>
        </div>
    </main>

@endsection

@section('footer-script')
    <script type="text/javascript">
        function deleteData(id) {
            $("#deleteForm").attr('action', `/sale-returns/${id}`);
        }

        function formSubmit() {
            $("#deleteForm").submit();
        }
    </script>
@stop
