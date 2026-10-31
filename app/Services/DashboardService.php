<?php


namespace App\Services;


use App\Account;
use App\AccountReview;
use App\Customer;
use App\Models\RandomReturn;
use App\Purchase;
use App\PurchaseReturn;
use App\Sale;
use App\SaleReturn;
use App\Services\Reports\CustomerReportServices;
use App\Supplier;
use App\Transaction;
use App\VoucherChartPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getAccountBalances()
    {
        if (hasPermission('today.income.expense') || hasPermission('available.amount')) {
            return $accounts = Account::getAccountWithBalance();
        }
        return collect([]);
    }

    public function yesterdayPurchaseTotal()
    {
        $returns = PurchaseReturn::companies()->where('date', Carbon::yesterday())->sum('amount');
        return Purchase::companies()->where('purchase_date', Carbon::yesterday())->select('id', 'invoice_discount')
            ->withCount(['purchase_details as total_purchase_amount' => function($q) {
                $q->select(DB::Raw('sum(product_sub_total)'));
            }])->get()->sum(function($item) {
                return $item->total_purchase_amount - $item->invoice_discount;
            }) - $returns;
    }

    public function getTodayTotalPurchaseAmount()
    {
        if (hasPermission('today.sale.purchase')) {
            $returns = PurchaseReturn::companies()->where('date', Carbon::today())->sum('amount');
            return Purchase::companies()->where('purchase_date', Carbon::today())->select('id', 'invoice_discount')
                ->withCount(['purchase_details as total_purchase_amount' => function($q) {
                    $q->select(DB::Raw('sum(product_sub_total)'));
                }])->get()->sum(function($item) {
                    return $item->total_purchase_amount - $item->invoice_discount;
                }) - $returns;
        }
        return 0;
    }


    public function getTotalMonthlyPurchaseAmount()
    {
        if (hasPermission('monthly.sale.purchase')) {
            $returns = PurchaseReturn::companies()->where('date', '>=', fdate(now(), 'Y-m-') . '01')->sum('amount');
            return Purchase::companies()->where('purchase_date', '>=', fdate(now(), 'Y-m-') . '01')->select('id', 'invoice_discount')
                ->withCount(['purchase_details as total_purchase_amount' => function($q) {
                    $q->select(DB::Raw('sum(product_sub_total)'));
                }])->get()->sum(function($item) {
                    return $item->total_purchase_amount - $item->invoice_discount;
                }) - $returns;
        }
        return 0;
    }

    public function yesterdaySaleTotal()
    {
        $returns = SaleReturn::companies()->where('date', Carbon::yesterday())->sum('amount');
        return Sale::companies()->where('sale_date', Carbon::yesterday())->select('id', 'invoice_discount', 'special_discount')
            ->withCount(['sale_details as total_sale_amount' => function($q) {
            $q->select(DB::Raw('sum(product_sub_total)'));
        }])->get()->sum(function($item) {
            return $item->total_sale_amount;
        });
    }
    public function todaySaleTotal()
    {
        if (hasPermission('today.sale.purchase')) {
            $returns = SaleReturn::companies()->where('date', Carbon::today())->sum('amount');
            return Sale::companies()->where('sale_date', Carbon::today())->select('id', 'invoice_discount', 'special_discount')
                ->withCount(['sale_details as total_sale_amount' => function($q) {
                    $q->select(DB::Raw('sum(product_sub_total)'));
                }])->get()->sum(function($item) {
                    return $item->total_sale_amount;
                });
        }
        return 0;
    }

    public function monthlySaleTotal()
    {
        if (hasPermission('monthly.sale.purchase')) {
            $returns = SaleReturn::companies()->where('date', '>=', fdate(now(), 'Y-m-') . '01')->sum('amount');
            return $sales = Sale::companies()->where('sale_date', '>=', fdate(now(), 'Y-m-') . '01')->select('id', 'invoice_discount', 'special_discount')
                ->withCount(['sale_details as total_sale_amount' => function($q) {
                    $q->select(DB::Raw('sum(product_sub_total)'));
                }])->get()->sum(function($t) {
                    return $t->total_sale_amount;
                });
        }
        return 0;
    }

    public function todayProfitTotal()
    {
        if (hasPermission('today.sale.purchase')) {
            $sales = Sale::companies()->where('sale_date', Carbon::today())
                ->withCount(['sale_details as total_sale_amount' => function($q) {
                    $q->select(DB::raw('sum(product_sub_total)'));
                }])
                ->withCount(['sale_details as total_purchase_amount' => function($q) {
                    $q->select(DB::raw('sum(quantity * product_cost)'));
                }])->get();

            $grossSales = $sales->sum('total_sale_amount');
            $totalDiscounts = $sales->sum('invoice_discount') + $sales->sum('special_discount');
            $returns = SaleReturn::companies()->where('date', Carbon::today())->sum('amount');
            $netSales = $grossSales - $totalDiscounts - $returns;
            $totalCOGS = $sales->sum('total_purchase_amount');

            return $netSales - $totalCOGS;
        }
        return 0;
    }

    public function monthlyProfitTotal()
    {
        if (hasPermission('monthly.sale.purchase')) {
            $startDate = fdate(now(), 'Y-m-') . '01';
            $sales = Sale::companies()->where('sale_date', '>=', $startDate)
                ->withCount(['sale_details as total_sale_amount' => function($q) {
                    $q->select(DB::raw('sum(product_sub_total)'));
                }])
                ->withCount(['sale_details as total_purchase_amount' => function($q) {
                    $q->select(DB::raw('sum(quantity * product_cost)'));
                }])->get();

            $grossSales = $sales->sum('total_sale_amount');
            $totalDiscounts = $sales->sum('invoice_discount') + $sales->sum('special_discount');
            $returns = SaleReturn::companies()->where('date', '>=', $startDate)->sum('amount');
            $netSales = $grossSales - $totalDiscounts - $returns;
            $totalCOGS = $sales->sum('total_purchase_amount');

            return $netSales - $totalCOGS;
        }
        return 0;
    }

    public function getTotalPayable()
    {
        return $totalOpening = Supplier::companies()->sum('current_balance');

        if (hasPermission('accounts.liabilities')) {
            $totalOpening = Supplier::companies()->sum('opening_due');

            $totalPurchaseAmount = Purchase::companies()->select('id', 'invoice_discount')
                ->withCount(['purchase_details as total_purchase_amount' => function($q) {
                    $q->select(DB::Raw('sum(product_sub_total)'));
                }])->get()->sum(function($item) {
                    return $item->total_purchase_amount - $item->invoice_discount;
                });

            $allPurchaseReturnAmount = -Transaction::companies()->whereHasMorph('transactionable', PurchaseReturn::class)
                ->with('transactionable')
                ->get()->sum('amount');

            $totalPayment = -Transaction::companies()
                ->where('amount', '<', 0)
                ->where(function ($q) {
                    $q->where(function ($qr){
                        $qr->whereHasMorph('transactionable', AccountReview::class, function ($q){
                            $q->where('transactionable_type', Supplier::class);
                        });
                    })->orWhere(function ($qr) {
                        $qr->whereHasMorph('transactionable', Purchase::class);
                    });
                })->sum('amount');

            return ($totalOpening + $totalPurchaseAmount - $totalPayment - $allPurchaseReturnAmount);
        }
        return 0;
    }

    public function getTodayVoucherAmount()
    {
        if (hasPermission('today.income.expense')) {
            return $todayVoucher = Transaction::companies()
                ->where('transactionable_type', VoucherChartPayment::class)
                ->where('transactionable_type', '!=', 'App\FundTransfer')
                ->where(function($q) {
                    $q->where('date', date('Y-m-d') . " 00:00:00")
                    ->orWhere('date', date('Y-m-d'));
                })
                ->get();
        }
        return collect([]);
    }

    public function getMonthlyVoucherAmount()
    {
        if (hasPermission('monthly.income.expense')) {

            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');

            return $monthlyVoucher = Transaction::companies()
                ->where('transactionable_type', VoucherChartPayment::class)
                ->where('transactionable_type', '!=', 'App\FundTransfer')
                ->where(function($q) use($startOfMonth) {
                    $q->where('date', '>=', $startOfMonth . " 00:00:00")
                        ->orWhere('date', '>=', $startOfMonth);
                })
                ->get();
        }
        return collect([]);
    }


    public function getTotalReceivable()
    {
        if (hasPermission('accounts.liabilities')) {
            return Customer::companies()->sum('current_balance');

            // $service    = new CustomerReportServices();
            // $customers  = Customer::companies()->get(['name', 'customer_previous_due', 'id']);
            // return $customerBalances = $service->getCustomersBalances($customers)->sum('balance');
        }
        return 0;
    }



    public function getOneWeekSalesAmount()
    {
        $sales_amounts = [];

        foreach (range(0, 6) as $day) {
            $sales_amounts[] = Sale::where('sale_date', Carbon::parse(today())->subDay(7 - $day)->format('Y-m-d'))->sum('total_payable');
        }

        return $sales_amounts;
    }

    public function getOneWeekPurchaseAmount()
    {
        $purchase_amounts = [];

        foreach (range(0, 6) as $day) {
            $purchase_amounts[] = Purchase::where('purchase_date', Carbon::parse(today())->subDay(7 - $day)->format('Y-m-d'))->sum('total_payable');
        }

        return $purchase_amounts;
    }

    public function getWeekDays()
    {
        $days = [];
        foreach (range(0, 6) as $day) {
            $days[] = Carbon::parse(today())->subDay(6-$day)->format('l');
        }
        return $days;
    }

    // ######################################################

    private function getReceivableTransactions()
    {
        return $transactions = Transaction::companies()
            ->whereIn('transactionable_type', [AccountReview::class, RandomReturn::class])
            ->whereHasMorph('transactionable', AccountReview::class, function ($q) {
                $q->where('transactionable_type', Customer::class)
                    ->where('amount', '>', 0);
            })->sum('amount');
    }

    private function getReceivableSale()
    {
        return $transactions = Transaction::companies()->whereIn('transactionable_type', [Sale::class])->whereHasMorph('transactionable', Sale::class)->get()->sum(function ($item) {
            return $item->transactionable->receive_amount;
        });
    }

    private function getReceivableReturn()
    {
        return $transactions = Transaction::companies()->whereIn('transactionable_type', [RandomReturn::class])
            ->whereHasMorph('transactionable', RandomReturn::class)
            ->get()->sum(function ($item) {
                return $item->transactionable->amount;
            });
    }

    public function getSales()
    {
        $sales = Sale::companies()->withCount(['sale_details AS total_amount' => function ($query) {
            $query->select(DB::raw("SUM(product_sub_total) as total"));
        }])->get();
        return $sales->sum('total_payable');
    }

}
