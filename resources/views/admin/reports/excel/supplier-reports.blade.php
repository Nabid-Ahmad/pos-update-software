<table>
    <thead>
        <tr>
            <th>Sl.</th>
            <th>Date</th>
            <th>Invoice Id</th>
            <th>Purpose</th>
            <th style="text-align: right">Debit / Purchase</th>
            <th style="text-align: right">Credit / Payment</th>
            <th>Balance</th>
        </tr>
    </thead>

    <tbody>
        @isset($transactions)
            <tr>
                <th colspan="6">Opening Balance:</th>
                <th style="text-align: right">{{ number_format($openingBalance, 2) }}</th>
            </tr>
            @php $sl = 1 @endphp
            @foreach($transactions->sortByDesc('date') as $key => $transaction)
                <tr>
                    <td>{{ $sl++ }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('Y-m-d') }}</td>
                    <td>{{ $transaction['invoice_id'] }}</td>
                    <td>{{ $transaction['type'] }} {{ ($transaction['discount'] ?? 0) > 0 ? ( ' (' .( $transaction['discount'] ?? 0) . ')') : '' }}</td>
                    <td style="text-align: right">{{ number_format($transaction['debit'], 2) }}</td>
                    <td style="text-align: right">{{ number_format($transaction['credit'], 2) }}</td>
                    <td style="text-align: right">
                        {{ number_format($openingBalance -= $transaction['credit'] - $transaction['debit'] + ($transaction['discount'] ?? 0), 2) }}
                    </td>
                </tr>
            @endforeach
        @endisset
    </tbody>
</table>
