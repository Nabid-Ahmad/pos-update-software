<?php

namespace App\Http\Controllers;

use App\Account;
use App\Models\RandomReturn;
use App\Product;
use App\ProductStock;
use Illuminate\Http\Request;
use App\AccountReview;
use App\Customer;
use App\Purchase;
use App\Sale;
use App\Supplier;
use App\Transaction;
use Illuminate\Support\Facades\DB;

class ProfitLossReportController extends Controller
{
    public function index(Request $request)
    {
        // Default to current month if no dates provided
        $from   = $request->from ?? date('Y-m-01');
        $to     = $request->to ?? date('Y-m-t');

        // A. Sales Revenue
        $salesQuery = Sale::companies()->whereBetween('sale_date', [$from, $to]);
        
        $sales = $salesQuery->withCount(['sale_details as total_sale_amount' => function($q) {
            $q->select(DB::raw('sum(product_sub_total)'));
        }])->withCount(['sale_details as total_purchase_amount' => function($q) {
            $q->select(DB::raw('sum(quantity * product_cost)'));
        }])->get();

        // 1. Gross Sales (without discount)
        $grossSales = $sales->sum('total_sale_amount');

        // 2. Total Discounts (invoice discount + special discount)
        $totalDiscounts = $sales->sum('invoice_discount') + $sales->sum('special_discount');

        // 3. Sales Returns (value of returned goods)
        $saleReturnTotal = \App\SaleReturn::companies()->whereBetween('date', [$from, $to])->sum('amount');
        $randomReturnTotal = \App\Models\RandomReturn::companies()->whereBetween('date', [$from, $to])->sum('amount');
        $salesReturnAmount = abs((float) ($saleReturnTotal + $randomReturnTotal));

        // Net Sales Revenue
        $netSalesRevenue = $grossSales - $totalDiscounts - $salesReturnAmount;

        // B. Cost of Goods Sold (COGS)
        // Total product cost for items sold in this period
        $totalCOGS = $sales->sum('total_purchase_amount');

        // C. Gross Profit
        $grossProfit = $netSalesRevenue - $totalCOGS;

        // D. Operating Expenses
        // Calculate all expenses within the date range
        $totalOperatingExpenses = Account::companies()->withCount(['expense_transactions as total_expense' => function($q) use ($from, $to) {
            $q->whereBetween('date', [$from, $to])->select(DB::raw('sum(amount)'));
        }])->get()->sum('total_expense');
        
        $totalOperatingExpenses = abs((float) $totalOperatingExpenses);

        // Fetch detailed expenses grouped by Chart of Account using Eloquent
        $expenseTransactions = \App\Transaction::companies()->with(['transactionable.voucher_account_chart.account_chart'])
            ->where('transactionable_type', 'App\VoucherChartPayment')
            ->where('amount', '<', 0)
            ->whereBetween('date', [$from, $to])
            ->get();

        $expensesDetails = $expenseTransactions->groupBy(function($item) {
                return $item->transactionable->voucher_account_chart->account_chart->head_name ?? 'Unknown Expense';
            })->map(function($group) {
                return (object)[
                    'head_name'    => $group->first()->transactionable->voucher_account_chart->account_chart->head_name ?? 'Unknown Expense',
                    'total_amount' => $group->sum(function($txn) { return abs((float) $txn->amount); })
                ];
            })->values();

        // E. Other Incomes (Credit Vouchers)
        $totalOtherIncome = Account::companies()->withCount(['income_transactions as total_income' => function($q) use ($from, $to) {
            $q->whereBetween('date', [$from, $to])->select(DB::raw('sum(amount)'));
        }])->get()->sum('total_income');
        
        $totalOtherIncome = abs((float) $totalOtherIncome);

        // Fetch detailed incomes grouped by Chart of Account using Eloquent
        $incomeTransactions = \App\Transaction::companies()->with(['transactionable.voucher_account_chart.account_chart'])
            ->where('transactionable_type', 'App\VoucherChartPayment')
            ->where('amount', '>', 0)
            ->whereBetween('date', [$from, $to])
            ->get();

        $incomeDetails = $incomeTransactions->groupBy(function($item) {
                return $item->transactionable->voucher_account_chart->account_chart->head_name ?? 'Unknown Income';
            })->map(function($group) {
                return (object)[
                    'head_name'    => $group->first()->transactionable->voucher_account_chart->account_chart->head_name ?? 'Unknown Income',
                    'total_amount' => $group->sum(function($txn) { return abs((float) $txn->amount); })
                ];
            })->values();

        // F. Net Profit
        $netProfit = $grossProfit + $totalOtherIncome - $totalOperatingExpenses;

        $data = [
            'from'                   => $from,
            'to'                     => $to,
            'grossSales'             => $grossSales,
            'totalDiscounts'         => $totalDiscounts,
            'salesReturnAmount'      => $salesReturnAmount,
            'netSalesRevenue'        => $netSalesRevenue,
            'totalCOGS'              => $totalCOGS,
            'grossProfit'            => $grossProfit,
            'totalOtherIncome'       => $totalOtherIncome,
            'incomeDetails'          => $incomeDetails,
            'totalOperatingExpenses' => $totalOperatingExpenses,
            'expensesDetails'        => $expensesDetails,
            'netProfit'              => $netProfit,
        ];

        return view('admin.reports.profit_loss_report', $data);
    }
}
