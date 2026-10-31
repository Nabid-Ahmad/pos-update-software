@extends('admin.master')
@section('title', ' - Liability History')

@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <a href="{{ route('liabilities.index') }}" class="btn btn-secondary pull-right">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                    <h3><i class="fa fa-history"></i> Payment History: {{ $liability->name }}</h3>
                    <p><strong>Description:</strong> {{ $liability->description }}</p>
                    <p><strong>Opening Balance:</strong> {{ number_format($liability->opening, 2) }}</p>
                    <p><strong>Current Balance:</strong> {{ number_format($liability->current_balance, 2) }}</p>
                    
                    <hr>

                    <div class="tile-body table-responsive">
                        <table class="table table-hover table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Account</th>
                                    <th>Added By</th>
                                    <th>Note / Reference</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($liability->transactions as $key => $transaction)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d M Y') }}</td>
                                        <td>
                                            @if($transaction->amount > 0)
                                                <span class="badge badge-info">Loan Taken</span>
                                            @else
                                                <span class="badge badge-success">Payment</span>
                                            @endif
                                        </td>
                                        <td>{{ optional($transaction->account)->account_name ?? 'N/A' }}</td>
                                        <td>{{ optional($transaction->created_user)->name ?? 'System' }}</td>
                                        <td>{{ $transaction->note ?? 'N/A' }}</td>
                                        <td class="text-right {{ $transaction->amount > 0 ? 'text-info' : 'text-success' }}">
                                            <strong>{{ number_format(abs($transaction->amount), 2) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No history found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-right">Total Loan Taken:</th>
                                    <th class="text-right text-info">
                                        <strong>{{ number_format($liability->transactions->where('amount', '>', 0)->sum('amount'), 2) }}</strong>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-right">Total Paid:</th>
                                    <th class="text-right text-success">
                                        <strong>{{ number_format(abs($liability->transactions->where('amount', '<', 0)->sum('amount')), 2) }}</strong>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-right">Current Balance:</th>
                                    <th class="text-right text-danger">
                                        <strong>{{ number_format($liability->current_balance, 2) }}</strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
