<?php


function accountLinked()
{
    return auth()->user()->company->account_linked;
}

function companyId()
{
    if (session()->has('fk_company_id') && session()->get('fk_company_id')) {
        return (int) session()->get('fk_company_id');
    }
    return auth()->check() ? (int) auth()->user()->fk_company_id : null;
}

function defaultAccount()
{
    $companyId = companyId();
    if (!$companyId) return null;

    $account = App\Account::where([
        'fk_company_id' => $companyId,
        'default_account' => 1
    ])->first();

    if (!$account) {
        $account = App\Account::where('fk_company_id', $companyId)->first();
    }

    // Auto-create a default Cash account if none exists for the new company
    if (!$account) {
        $account = App\Account::create([
            'account_name' => 'Cash Account',
            'account_no' => 'CASH-01',
            'branch_name' => 'Main Branch',
            'opening_balance' => 0,
            'default_account' => 1,
            'status' => 1,
            'fk_company_id' => $companyId,
        ]);
    }

    return $account;
}

function flipDate($date)
{
    return date('Y-m-d', strtotime($date));
}

function dateRange()
{
    $request = request();
    return [
        'from' => $request->from ? $request->from : date('Y-m-d'),
        'to' => $request->to ? $request->to : date('Y-m-d'),
    ];
}