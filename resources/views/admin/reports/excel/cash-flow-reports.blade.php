@php
    function getModelValueLocal($model) {
        $models = ['App\Sale', 'App\Purchase', 'App\FundTransfer', 'App\VoucherChartPayment', 'App\AccountReview', 'App\Models\RandomReturn'];
        $values = ['Sale', 'Purchase', 'Fund Transfer', 'Voucher', 'Payment', 'Product Return'];

        $index = array_search($model, $models);
        if($index !== false) {
            return $values[$index];
        } else {
            return $model;
        }
    }

    function getTransactionDetailsExcel($transaction) {
        $type = $transaction->transactionable_type;
        $amount = $transaction->amount;
        $ref = '';
        $desc = '';
        $party = '-';

        if ($type == 'App\Sale') {
            $sale = $transaction->transactionable;
            if ($sale) {
                $ref = '#S-' . $sale->sale_invoice_id;
                $party = optional($sale->customer)->name ?? '-';
                if ($amount > 0) {
                    $desc = 'Received Payment for Sale';
                } else {
                    $desc = 'Paid Delivery Charge for Sale';
                }
            } else {
                $desc = 'Sale Transaction';
            }
        } elseif ($type == 'App\Purchase') {
            $purchase = $transaction->transactionable;
            if ($purchase) {
                $ref = $purchase->invoiceId;
                $party = optional($purchase->supplier)->name ?? '-';
                $desc = 'Paid for Purchase';
            } else {
                $desc = 'Purchase Transaction';
            }
        } elseif ($type == 'App\VoucherChartPayment') {
            $vcp = $transaction->transactionable;
            if ($vcp && $vcp->voucher) {
                $ref = $vcp->voucher->voucher_reference ?? ('#V-' . $vcp->voucher->id);
                $party = optional($vcp->voucher->party)->name ?? '-';
                $desc = 'Voucher Payment';
            } else {
                $desc = 'Voucher Payment';
            }
        } elseif ($type == 'App\AccountReview') {
            $ar = $transaction->transactionable;
            if ($ar) {
                $ref = $ar->invoice_id;
                $party = optional($ar->consumer)->name ?? '-';
                $desc = 'Account Payment';
            } else {
                $desc = 'Payment';
            }
        } elseif ($type == 'App\FundTransfer') {
            $desc = 'Fund Transfer';
        } else {
            $desc = str_replace('App\\', ' ', getModelValueLocal($type));
        }

        return [
            'ref' => $ref,
            'desc' => $desc,
            'party' => $party
        ];
    }
@endphp

<table>
    <thead>
        <tr>
            <th colspan="7" style="text-align: center; font-size: 16px; font-weight: bold;">Cash Flow Report</th>
        </tr>
        @if (request('from'))
            <tr>
                <th colspan="7" style="text-align: center; font-weight: bold;">
                    From: {{ request('from') }} To: {{ request('to') }}
                </th>
            </tr>
        @endif
        <tr>
            <th>Sl.</th>
            <th>Date</th>
            <th>Invoice/Ref</th>
            <th>Account</th>
            <th>Detailed Description</th>
            <th style="text-align: right">Dr. Amount (OUT)</th>
            <th style="text-align: right">Cr. Amount (IN)</th>
            <th style="text-align: right">Balance</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <th colspan="7">Opening Balance</th>
            <th style="text-align: right">{{ number_format($openingBalance, 2) }}</th>
        </tr>

        @php 
            $sl = 1; 
            $currentBalance = $openingBalance;
        @endphp
        @isset($transactions)
            @foreach($transactions as $key => $transaction)
                @php 
                    $currentBalance += $transaction->amount;
                    $details = getTransactionDetailsExcel($transaction);
                @endphp
                <tr>
                    <td>{{ $sl++ }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction->date)->format('Y-m-d') }}</td>
                    <td>{{ $details['ref'] }}</td>
                    <td>{{ optional($transaction->account)->account_name ?? '-' }}</td>
                    <td>{{ $details['desc'] }}</td>
                    <td style="text-align: right">
                        {{ $transaction->amount < 0 ? number_format(-$transaction->amount, 2) : '' }}
                    </td>
                    <td style="text-align: right">
                        {{ $transaction->amount > 0 ? number_format($transaction->amount, 2) : '' }}
                    </td>
                    <td style="text-align: right">{{ number_format($currentBalance, 2) }}</td>
                </tr>
            @endforeach
        @endisset
    </tbody>
</table>
