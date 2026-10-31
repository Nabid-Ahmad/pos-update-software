<table>
    <thead>
        <tr>
            <th>Sl.</th>
            <th>Date</th>
            <th>Invoice Id</th>
            <th>Description</th>
            <th style="text-align: right">Debit/Sales</th>
            <th style="text-align: right">Credit/Receive</th>
            <th style="text-align: right">Balance</th>
        </tr>
    </thead>

    <tbody>
        @isset($transactions)
            <tr>
                <th colspan="6">Opening Balance</th>
                <th style="text-align: right">{{ number_format($openingBalance, 2) }}</th>
            </tr>


            @php $sl = 1 @endphp
            @foreach($transactions as $key => $transaction)
                <tr>
                    <td>{{ $sl++ }}</td>
                    <td> {{ $transaction['date'] }} </td>
                    <td>
                        @if($transaction['type'] == 'Sale')
                            <a href="{{ route('sales.show', $transaction['source_id']) }}?print_type=invoice" target="_blank">{{ $transaction['invoice_id'] }}</a>
                        @else
                            <a>{{ $transaction['invoice_id'] }}</a>
                        @endif
                    </td>
                    <td> {{ $transaction['type'] }} {{ ($transaction['discount'] ?? 0) > 0 ? ( ' (' . ($transaction['discount'] ?? 0) . ')') : '' }} </td>
                    <td style="text-align: right">{{ number_format(abs($transaction['debit']), 2) }}</td>
                    <td style="text-align: right">{{ number_format(abs($transaction['credit']), 2) }}</td>
                    <td style="text-align: right">
                        {{ number_format($openingBalance += ($transaction['debit'] - $transaction['credit'] - ($transaction['discount'] ?? 0)), 2) }}
                    </td>
                </tr>
            @endforeach
        @endisset
    </tbody>
</table>