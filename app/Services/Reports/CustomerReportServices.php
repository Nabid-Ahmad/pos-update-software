<?php


namespace App\Services\Reports;


use App\AccountReview;
use App\Customer;
use App\Models\RandomReturn;
use App\Sale;
use App\SaleReturn;
use App\Transaction;
use Carbon\Carbon;

class CustomerReportServices
{
    private $fromDate;
    private $toDate;
    private $allSales;
    private $allSaleReturns;
    private $allSaleReturnUnpaids;
    private $allTransactions;
    private $customerId;
    private $customer;

    public function __construct()
    {
        $this->fromDate = Carbon::parse(request('from') ?? "2010-01-01")->format('Y-m-d');
        $this->toDate   = Carbon::parse(request('to') ?? now())->format('Y-m-d');
    }

    public function getAllCustomerReportData()
    {
        $request = request();

        if ($request->filled('customer_id')) {
            $customer_id    = $request->customer_id;

            $this->customerId = $customer_id;

            // get all purchase and transaction
            $allSales               = $this->getAllSales($customer_id);
            $allSaleReturns         = $this->getSaleReturnPaids($customer_id);
            // $allSaleReturnUnpaids   = $this->getSaleReturnUnpaids($customer_id);
            $allTransactions        = $this->getCustomerTransactions($customer_id);

           

            // set data as global
            $this->allSales             = $allSales;
            $this->allSaleReturns       = $allSaleReturns;
            $this->allTransactions      = $allTransactions;

            // get data for filter date
            $sales              = $allSales->where('date', '>=', $this->fromDate)->where('date', '<=', $this->toDate);
            $saleReturns        = $allSaleReturns->where('date', '>=', $this->fromDate)->where('date', '<=', $this->toDate);
            // $saleReturnUnpaids  = $allSaleReturnUnpaids->where('date', '>=', $this->fromDate)->where('date', '<=', $this->toDate);
            $transactions       = $allTransactions->where('date', '>=', $this->fromDate)->where('date', '<=', $this->toDate);

            if ($request->filled('supplier_id')) {
                $supplier_id = $request->supplier_id;
                $sales = $sales->where('supplier_id', $supplier_id);
                // Return random_returns are not supplier-specific, exclude them
                $saleReturns = collect([]);
                $transactions = $transactions->where('fk_supplier_id', $supplier_id);
            }


            $customerData = [];
            if (count($transactions) > 0) {
                $customerData = $transactions->merge($saleReturns);
            } else {
                $customerData = $saleReturns;
            }

            if (count($sales) > 0) {
                $customerData = $sales->merge($customerData);
            } else {
                $customerData = $customerData;
            }
            
            // if (count($saleReturnUnpaids) > 0) {
            //     $customerData = $saleReturnUnpaids->merge($customerData);
            // } else {
            //     $customerData = $customerData;
            // }

            return $customerData->sortBy('date');

            return ($sales->merge($transactions->merge($saleReturns)))->sortBy('date');
        }
        return [];

    }

    public function getOpeningBalanceFromDate()
    {
        if (!request('customer_id')) {
            return 0;
        }
        $customer       = Customer::find($this->customerId);
        $this->customer = $customer;
        $openingDue     = $customer->customer_previous_due;

        $fromPurchaseAmount = clone $this->allSales;
        $fromSaleReturnPaidAmount = clone $this->allSaleReturns;
        $fromTransactionAmount = clone $this->allTransactions;

        if (request()->filled('supplier_id')) {
            $supplier_id = request()->supplier_id;
            $fromPurchaseAmount = $fromPurchaseAmount->where('supplier_id', $supplier_id);
            $fromSaleReturnPaidAmount = collect([]);
            $fromTransactionAmount = $fromTransactionAmount->where('fk_supplier_id', $supplier_id);
            
            // If filtering by supplier, opening due should not include the global customer previous due 
            // unless we only track opening due per supplier, which we have in CustomerSupplier but it's not simple.
            // Since previous_due is global, we should set it to 0 when filtering by supplier.
            $openingDue = 0;
            $customerSupplier = \App\Models\CustomerSupplier::where('customer_id', $this->customerId)->where('supplier_id', $supplier_id)->first();
            if ($customerSupplier) {
                $openingDue = $customerSupplier->opening_balance ?? 0;
            }
        }

        $fromPurchaseAmount = $fromPurchaseAmount->where('date', '<', $this->fromDate)->sum(function ($item) {
            return $item['debit'] - $item['credit'];
        });

        $fromSaleReturnPaidAmount = $fromSaleReturnPaidAmount->where('date', '<', $this->fromDate)->sum(function ($item) {
            return $item['debit'] - $item['credit'];
        });

        $fromTransactionAmount = $fromTransactionAmount->where('date', '<', $this->fromDate)->sum(function ($item) {
            return $item['debit'] - $item['credit'];
        });

        // calculate opening balance for from date
        return $openingBalance = ($openingDue + $fromPurchaseAmount + $fromTransactionAmount + $fromSaleReturnPaidAmount);
    }

    public function getCustomer()
    {
        return $this->customer;
    }



    private function getAllSales($customer_id)
    {
        return $purchases = Sale::companies()
            ->withCount(['sale_details AS total_amount' => function ($query) {
                $query->select(\DB::raw("SUM(product_sub_total) as total"));
            }])->where('fk_customer_id', $customer_id)->get()

            ->map(function($item) {
                $data['date'] = Carbon::parse($item->sale_date)->format('Y-m-d');
                $data['invoice_id'] = $item->invoiceId;
                $data['source_id']  = $item->id;
                $data['supplier_id'] = $item->supplier_id;
                
                $courier = ($item->delivery_charge_payer === 'customer') ? floatval($item->currier_amount ?? $item->delivery_charge ?? 0) : 0;
                $data['debit']      = $item->total_amount + $item->invoice_tax + $courier + ($item->cod_amount ?? 0) - ($item->invoice_discount + $item->special_discount);
                $data['credit']     = $item->payments()->where('amount', '>', 0)->sum('amount');
                $data['type']       = 'Sale';

                return $data;
            });
    }

    private function getCustomerTransactions($customer_id)
    {
        return $transactions = Transaction::whereHasMorph('transactionable', AccountReview::class, function ($q) use ($customer_id) {
                $q->where('transactionable_type', Customer::class)
                    ->where('transactionable_id', $customer_id);
            })
            ->with('transactionable')
            ->get()->map(function($item) {
                $data['date'] = Carbon::parse($item->transactionable->date)->format('Y-m-d');
                $data['invoice_id'] = $item->transactionable->invoiceId;
                $data['discount']   = $item->transactionable->discount;
                $data['source_id']  = $item->transactionable_id;
                $data['fk_supplier_id'] = $item->transactionable->fk_supplier_id;
                $data['debit'] = 0;
                $data['credit'] = abs($item->amount);
                $data['type'] = 'Collection';

                return $data;
            });
    }

    private function getSaleReturnPaids($customer_id)
    {
        $transactions = Transaction::whereHasMorph('transactionable', RandomReturn::class, function ($q) use ($customer_id) {
                $q->where('fk_customer_id', $customer_id)->companies();
            })
            ->with('transactionable')
            ->get()
            ->map(function($item) {
                $data['date'] = Carbon::parse($item->transactionable->date)->format('Y-m-d');
                $data['invoice_id'] = $item->transactionable->invoiceId ?? ('#SR-' . (1000000 + $item->transactionable_id));
                $data['source_id']  = $item->transactionable_id;
                $data['debit'] = 0;
                $data['credit'] = abs($item->transactionable->amount);
                $data['type'] = 'Sale Return Payment';

                return $data;
            });


        
        // $transactions2 = Transaction::whereHasMorph('transactionable', SaleReturn::class, function ($q) use ($customer_id) {
        //     $q->where('customer_id', $customer_id);
        // })
        // ->with('transactionable')
        // ->get()
        // ->map(function($item) {
        //     $data['date'] = Carbon::parse($item->transactionable->date)->format('Y-m-d');
        //     $data['invoice_id'] = $item->transactionable->invoice_no ?? ('#SR-' . (1000000 + $item->transactionable_id));
        //     $data['source_id']  = $item->transactionable_id;
        //     $data['debit'] = 0;
        //     $data['credit'] = abs($item->transactionable->amount);
        //     $data['type'] = 'Sale Return Payment';

        //     return $data;
        // });
        $transactions2 = SaleReturn::where('customer_id', $customer_id)
            // ->where('paid_amount', 0)
            ->get()
            ->map(function($item) {
                $data['date']       = fdate($item->date);
                $data['invoice_id'] = $item->invoice_no ?? ('#SR-' . (1000000 + $item->id));
                $data['source_id']  = $item->transactionable_id;
                $data['debit']      = $item->paid_amount;
                $data['credit']     = $item->amount;
                $data['type']       = 'Sale Return';

                return $data;
            });
        if (count($transactions) > 0) {
            return array_merge($transactions, $transactions2);
        }
        return $transactions2;
    }


    private function getSaleReturnUnpaids($customer_id)
    {
        return $transactions = SaleReturn::where('customer_id', $customer_id)->where('paid_amount', 0)
            ->get()
            ->map(function($item) {
                $data['date']       = fdate($item->date);
                $data['invoice_id'] = $item->invoice_no ?? ('#SR-' . (1000000 + $item->id));
                $data['source_id']  = $item->transactionable_id;
                $data['debit']      = $item->amount;
                $data['credit']     = 0;
                $data['type']       = 'Sale Return';

                return $data;
            });
    }

    //     #############################         CUSTOMER BALANCE      ###############################
    public function getAllSalesAmount($customer_id, $to_date = null, $supplier_id = null)
    {
        $sales = Sale::companies()
        ->withCount(['sale_details AS total_amount' => function ($query) {
            $query->select(\DB::raw("SUM(product_sub_total) as total"));
            }])->where('fk_customer_id', $customer_id)
            ->when($supplier_id, function ($q) use ($supplier_id) {
                $q->where('supplier_id', $supplier_id);
            })
            ->when($to_date, function ($q) use ($to_date) {
                $q->where('sale_date', '<=', $to_date);
            })
            ->get()
            ->sum(function ($item) use ($to_date) {
                // Invoice Discount & Special Discount are stored as flat values in the database after conversion
                $invoiceDiscount = $item->invoice_discount;
                $specialDiscount = $item->special_discount;

                $courier = ($item->delivery_charge_payer === 'customer') ? floatval($item->currier_amount ?? $item->delivery_charge ?? 0) : 0;
                
                $paid = $item->payments()
                    ->where('amount', '>', 0)
                    ->when($to_date, function ($q) use ($to_date) {
                        $q->where('date', '<=', $to_date);
                    })
                    ->sum('amount');

                return $item->total_amount + $item->invoice_tax + $courier + ($item->cod_amount ?? 0) - $invoiceDiscount - $specialDiscount - $paid;
            });
            return $sales;
    }

    public function getAllReturnAmount($customer_id, $to_date = null, $supplier_id = null)
    {
        return $saleReturns = SaleReturn::where('customer_id', $customer_id)
        ->when($supplier_id, function ($q) use ($supplier_id) {
            $q->where('supplier_id', $supplier_id);
        })
        ->when($to_date, function ($q) use ($to_date) {
            $q->where('date', '<=', $to_date);
        })
        ->get()
        ->sum(function($item) {
            return ($item->amount - ($item->exchange_amount ?? 0)) - ($item->paid_amount ?? 0);
        });
    }

    public function getAllSalesReturnAmount($customer_id, $to_date = null, $supplier_id = null)
    {
        $oldReturnAmount = Transaction::whereHasMorph('transactionable', RandomReturn::class, function ($q) use ($customer_id) {
            $q->where('fk_customer_id', $customer_id);
        })
        ->when($supplier_id, function ($q) {
            $q->whereRaw('1 = 0');
        })
        ->when($to_date, function ($q) use ($to_date) {
            $q->where('date', '<=', $to_date);
        })
        ->with('transactionable')
        ->get()->sum(function ($item) {
            return $item->transactionable->amount;
        });

        $newReturnAmount = Transaction::whereHasMorph('transactionable', SaleReturn::class, function ($q) use ($customer_id, $supplier_id) {
            $q->where('customer_id', $customer_id)
              ->when($supplier_id, function($sq) use ($supplier_id) {
                  $sq->where('supplier_id', $supplier_id);
              });
        })
        ->when($to_date, function ($q) use ($to_date) {
            $q->where('date', '<=', $to_date);
        })
        ->with('transactionable')
        ->get()->sum(function ($item) {
            return $item->transactionable->amount;
        });

        return ($oldReturnAmount + $newReturnAmount);

    }

    public function getCustomerTransactionAmount($customer_id, $to_date = null, $supplier_id = null)
    {
        return $transactions = - (Transaction::whereHasMorph('transactionable', AccountReview::class, function ($q) use ($customer_id, $supplier_id) {
            $q->where('transactionable_type', Customer::class)
                ->where('transactionable_id', $customer_id)
                ->when($supplier_id, function($sq) use ($supplier_id) {
                    $sq->where('fk_supplier_id', $supplier_id);
                });
        })
        ->when($to_date, function ($q) use ($to_date) {
            $q->where('date', '<=', $to_date);
        })
        ->get()->sum(function ($item) {
            
            return ($item->amount + $item->transactionable->discount);
        }));
    }

    public function getCustomersBalances($customers, $to_date = null, $supplier_id = null)
    {
        $balances = $customers->map(function ($item) use ($to_date, $supplier_id) {
            $allReturnAmount            = $this->getAllReturnAmount($item->id, $to_date, $supplier_id);
            $allSalesAmount             = $this->getAllSalesAmount($item->id, $to_date, $supplier_id);
            $allSalesReturnPaidAmount   = $this->getAllSalesReturnAmount($item->id, $to_date, $supplier_id);
            $allPaymentAmount           = $this->getCustomerTransactionAmount($item->id, $to_date, $supplier_id);
            
            if ($supplier_id) {
                $cs = \App\Models\CustomerSupplier::where('customer_id', $item->id)->where('supplier_id', $supplier_id)->first();
                $openingDueBalance = optional($cs)->opening_balance ?? 0;
            } else {
                $openingDueBalance = optional($item)->customer_previous_due ?? 0;
            }

            $item['balance']            = ($openingDueBalance + $allSalesAmount + $allPaymentAmount + $allSalesReturnPaidAmount - $allReturnAmount);
            
            return $item;
        });
        
        return $balances;
    }
}
