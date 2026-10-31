<?php


namespace App\Services\Reports;


use App\Account;
use App\Transaction;

class CashFlowReportService
{
    public function getCashFlowReportData($request)
    {
        $data['openingBalance'] = 0;

        $query = Transaction::companies()->with(['account', 'transactionable'])->orderBy('date');
        if ($request->filled('account_id')) {
            $query->where('fk_account_id', $request->account_id);
        }
        if ($request->filled('from')) {
            $trans = Transaction::where('fk_company_id', companyId())->orderBy('date');
            if ($request->filled('account_id')) {
                $trans->where('fk_account_id', $request->account_id);
            }
            $openingBalance = $trans->where('date', '<', $request->from)->get();
            $data['openingBalance'] = $openingBalance->sum('amount');
        }
        if ($request->filled('from')) {
            $query->where('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('date', '<=', $request->to);
        }
        $transactions = $query->get();
        $data['transactions'] = $transactions;

        // Calculate summary totals
        $totalInflow = 0;
        $totalOutflow = 0;
        foreach ($transactions as $tx) {
            if ($tx->amount > 0) {
                $totalInflow += $tx->amount;
            } else {
                $totalOutflow += abs($tx->amount);
            }
        }
        $data['totalInflow'] = $totalInflow;
        $data['totalOutflow'] = $totalOutflow;
        $data['netCashPosition'] = $data['openingBalance'] + $totalInflow - $totalOutflow;

        $data['accounts'] = Account::companies()->pluck('account_name','id');
        return $data;
    }
}
