@extends('admin.master')
@section('title', ' - Collection Invoice')

@push('style')
    <style type="text/css">
        @media print {
            .d-none, .no-print { display: none !important; }
            .d-print { display: block !important; }
            @page { margin: 0.5in; }
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            background: #fff;
        }
        .table-custom th, .table-custom td { padding: 10px; border: 1px solid #ddd; }
    </style>
@endpush

@section('content')
<main class="app-content">
    <div class="row">
        <div class="col-md-12 text-center mb-3 no-print">
            <button class="btn btn-primary" onclick="window.print()"><i class="fa fa-print"></i> Print Receipt</button>
            <button class="btn btn-danger" onclick="window.close()"><i class="fa fa-close"></i> Close</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="invoice-box">
                <div class="row mb-4 pb-3" style="border-bottom: 2px solid #333;">
                    <div class="col-sm-6">
                        @if(!empty($company->company_logo))
                            <img src="{{ asset($company->company_logo) }}" style="max-height: 80px;">
                        @endif
                        <h2>{{ $company->name }}</h2>
                        <p class="mb-0">{{ $company->address }}</p>
                        <p class="mb-0">Phone: {{ $company->phone }}</p>
                    </div>
                    <div class="col-sm-6 text-right">
                        <h2 style="color: #00728c;">MONEY RECEIPT</h2>
                        <h5>Receipt No: {{ $customerCollection->invoice_id }}</h5>
                        <p>Date: {{ fdate($customerCollection->date ?? $customerCollection->created_at, 'Y-m-d') }}</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-12">
                        <h5>Received From:</h5>
                        <p style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">{{ optional($customerCollection->transactionable)->name }}</p>
                        <p class="mb-0">Phone: {{ optional($customerCollection->transactionable)->phone }}</p>
                        <p class="mb-0">Address: {{ optional($customerCollection->transactionable)->address }}</p>
                    </div>
                </div>

                <table class="table table-custom w-100 mb-4">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th width="70%">Description</th>
                            <th width="30%" class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                Payment Received {{ $customerCollection->type == 'advance' ? '(Due Collection)' : '(' . ucfirst($customerCollection->type) . ')' }}
                                @if($customerCollection->payment_method)
                                    <br><small>Method: {{ $customerCollection->payment_method->method_name }}</small>
                                @endif
                                @if($customerCollection->account)
                                    <br><small>Account: {{ $customerCollection->account->account_name }}</small>
                                @endif
                                @if($customerCollection->note)
                                    <br><small>Note: {{ $customerCollection->note }}</small>
                                @endif
                            </td>
                            @php
                                $customer = $customerCollection->transactionable;
                                $date = $customerCollection->date ?? $customerCollection->created_at->format('Y-m-d');
                                
                                // Fetch historical balance up to the receipt date
                                $service = new \App\Services\Reports\CustomerReportServices();
                                $currentBalance = $service->getCustomersBalances(collect([$customer]), $date)->first()->balance ?? 0;
                                
                                $paymentAmount = abs($customerCollection->amount);
                                
                                // Payment reduces due. So previous balance = current balance + payment
                                $previousBalance = $currentBalance + $paymentAmount;
                                
                                $prevText = $previousBalance > 0 ? 'Previous Due' : ($previousBalance < 0 ? 'Previous Advance' : 'Previous Balance');
                                $currText = $currentBalance > 0 ? 'Current Due' : ($currentBalance < 0 ? 'Current Advance' : 'Current Balance');
                            @endphp
                            <td class="text-right font-weight-bold" style="font-size: 16px;">
                                ৳ {{ number_format($paymentAmount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right"><strong>{{ $prevText }} :</strong></td>
                            <td class="text-right">৳ {{ number_format(abs($previousBalance), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-right"><strong>{{ $currText }} :</strong></td>
                            <td class="text-right">৳ {{ number_format(abs($currentBalance), 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="row mt-5 pt-5">
                    <div class="col-sm-6">
                        <div style="border-top: 1px solid #333; width: 200px; text-align: center;">
                            Customer Signature
                        </div>
                    </div>
                    <div class="col-sm-6 text-right">
                        <div style="border-top: 1px solid #333; width: 200px; text-align: center; float: right;">
                            Authorized Signature<br>
                            <small>{{ optional($customerCollection->created_user)->name }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
