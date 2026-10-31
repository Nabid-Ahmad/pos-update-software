
@extends('admin.master')
@section('title', ' - Liabilities')

@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    @if(hasPermission('accounts.create'))
                        <a href="{{ route('liabilities.create') }}" class="btn btn-primary pull-right">
                            <i class="fa fa-plus"></i> Add Liability
                        </a>
                    @endif
                    <h3><i class="fa fa-th-list"></i> Liability List</h3>

                    <div class="tile-body" style="height: 740px !important;">

                        <!-- filter -->
                        <div class="row">
                            <div class="col-sm-8 mx-auto">
                                <form action="" method="get">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Search Liability</span>
                                        </div>
                                        <input class="form-control form-control-md" name="search" placeholder="Search anything.........." value="{{ request('search') }}">
                                        <div class="input-group-append">
                                            <button style="cursor: pointer" type="submit" class="input-group-text btn-sm"><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="tile-body table-responsive">
                            <table class="table table-hover table-bordered table-sm" id="sampleTable">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Liability Name</th>
                                        <th>Description</th>
                                        <th class="text-center">Method (Account)</th>
                                        <th class="text-center">Opening (Taken)</th>
                                        <th class="text-center">Paid</th>
                                        <th class="text-center">Current Balance</th>
                                        <th width="10%" class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php $totalPaidSum = 0; @endphp
                                    @foreach($liabilities as $key => $liability)
                                        @php
                                            $initialTransaction = $liability->transactions->where('amount', '>', 0)->first();
                                            $method = $initialTransaction ? (optional($initialTransaction->account)->account_name ?? 'N/A') : 'N/A';
                                            $paidAmount = abs($liability->transactions->where('amount', '<', 0)->sum('amount'));
                                            $totalPaidSum += $paidAmount;
                                        @endphp
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $liability->name }}</td>
                                            <td>{{ $liability->description }}</td>
                                            <td class="text-center"><span class="badge badge-info">{{ $method }}</span></td>
                                            <td class="text-right">{{ $liability->opening }}</td>
                                            <td class="text-right text-success">{{ number_format($paidAmount, 2) }}</td>
                                            <td class="text-right text-danger">{{ $liability->current_balance }}</td>
                                            <td class="text-center">
                                                <div class="btn-group btn-corner">
                                                    @if(hasPermission('accounts.edit'))
                                                        <a class="btn btn-warning btn-sm" title="History" href="{{ route('liabilities.show', $liability->id) }}">
                                                            <i class="fa fa-history"></i>
                                                        </a>
                                                        <a class="btn btn-success btn-sm" title="Payment" href="javascript:void(0)" data-toggle="modal" data-target="#paymentModal" onclick="setLiabilityPaymentData('{{ $liability->id }}', '{{ $liability->name }}')">
                                                            <i class="fa fa-money"></i>
                                                        </a>
                                                        <a class="btn btn-info btn-sm" title="Edit"
                                                           href="{{ route('liabilities.edit', $liability->id) }}"> <i class="fa fa-edit"></i>
                                                        </a>
                                                    @endif

                                                    @if($liability->default_account == 1)
                                                        <button class="btn btn-danger btn-sm" disabled="" title="Can not Delete"><i class="fa fa-trash"></i></button>
                                                    @elseif(hasPermission('accounts.destroy'))
                                                        <button type="button" class="btn btn-danger btn-sm" title="Delete" onclick="delete_item('{{ route('liabilities.destroy', $liability->id) }}')">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right"><strong>Total:</strong></th>
                                        <th class="text-right">{{ number_format($liabilities->sum('opening'), 2) }}</th>
                                        <th class="text-right text-success">{{ number_format($totalPaidSum, 2) }}</th>
                                        <th class="text-right text-danger">{{ number_format($liabilities->sum('current_balance'), 2) }}</th>
                                        <th class="text-right"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- delete form -->
    @include('partials._delete_form')

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Liability Payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('liabilities.payment') }}" method="POST" id="liabilityPaymentForm">
                        @csrf
                        <input type="hidden" name="liability_id" id="payment_liability_id">
                        
                        <div class="form-group row">
                            <label class="control-label col-md-4">Liability Name</label>
                            <div class="col-md-8">
                                <input id="payment_liability_name" class="form-control" type="text" readonly>
                            </div>
                        </div>

                        <div class="form-group row add_asterisk">
                            <label class="control-label col-md-4">Account</label>
                            <div class="col-md-8">
                                <select name="account_id" class="form-control select2" required>
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row add_asterisk">
                            <label class="control-label col-md-4">Amount</label>
                            <div class="col-md-8">
                                <input name="amount" class="form-control" type="number" step="0.01" placeholder="Enter amount" required>
                            </div>
                        </div>

                        <div class="form-group row add_asterisk">
                            <label class="control-label col-md-4">Date</label>
                            <div class="col-md-8">
                                <input name="date" class="form-control" type="date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="control-label col-md-4">Note / Reference</label>
                            <div class="col-md-8">
                                <textarea name="note" class="form-control" rows="2" placeholder="Note"></textarea>
                            </div>
                        </div>

                        <div class="tile-footer text-right">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-fw fa-lg fa-check-circle"></i>Submit Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-script')
    <script src="{{ asset('assets/admin/js/jquery-3.2.1.min.js') }}"></script>
    <script>
        function setLiabilityPaymentData(id, name) {
            $('#payment_liability_id').val(id);
            $('#payment_liability_name').val(name);
        }
    </script>
@endsection
