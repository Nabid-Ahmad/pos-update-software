<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountReview;
use App\Customer;
use App\Http\Requests\DueRequest;
use App\Models\CustomerSupplier;
use App\PaymentMethod;
use App\Services\PurchaseService;
use App\Services\Reports\CustomerReportServices;
use App\Services\SalesService;
use App\Supplier;
use Illuminate\Http\Request;

class DueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->type) {
            $query = AccountReview::userLog()->whereHasMorph('transactionable', $request->type == 'customer' ? [Customer::class] : [Supplier::class], function ($q) {
                $q->where('fk_company_id', companyId());
            });

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            } elseif ($request->filled('start_date')) {
                $query->where('date', '>=', $request->start_date);
            } elseif ($request->filled('end_date')) {
                $query->where('date', '<=', $request->end_date);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $cleanSearch = (int)str_ireplace(['#c-', 'c-', '#C-', 'C-'], '', $search);
                $query->where(function($q) use ($search, $cleanSearch) {
                    $q->where('id', 'LIKE', "%{$search}%")
                      ->orWhere('transactionable_id', $cleanSearch);
                });
            }

            if ($request->filled('party_id')) {
                $query->where('transactionable_id', $request->party_id);
            }
            if ($request->filled('supplier_id')) {
                $query->where('fk_supplier_id', $request->supplier_id);
            }

            $reviews = $query->with('account', 'payment_method', 'supplier')
            ->orderByDesc('id')
            ->paginate();

            if ($request->type == 'customer') {
                $parties = Customer::companies()->get()->mapWithKeys(function ($c) {
                    return [$c->id => $c->name . ' (' . $c->customerId . ')'];
                });
                $suppliers = Supplier::companies()->pluck('name', 'id');
            } else {
                $parties = Supplier::companies()->pluck('name', 'id');
                $suppliers = collect();
            }

            return view('admin.due.index', compact('reviews', 'parties', 'suppliers'));
        }
        return abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $accounts       = Account::getAccountWithBalance();
        $paymentMethods = PaymentMethod::companies()->where('status', 1)->pluck('method_name', 'id');
        $suppliers = Supplier::companies()->get();

        if($request->type == 'customer') {
            return view('admin.due.customer', compact('accounts', 'paymentMethods','suppliers'));
        }
        if($request->type == 'supplier') {
            $selected_supplier = $request->filled('supplier_id') ? Supplier::companies()->find($request->supplier_id) : null;
            return view('admin.due.supplier', compact('accounts', 'paymentMethods','suppliers', 'selected_supplier'));
        }
        abort(404);
    }
    

    public function supplierwiseDue(Request $request)
    {
        $accounts       = Account::getAccountWithBalance();
        $paymentMethods = PaymentMethod::companies()->where('status', 1)->pluck('method_name', 'id');
        $suppliers      = Supplier::companies()->get();

        if($request->type == 'customer') {
            return view('admin.due.supplierwise-due', compact('accounts', 'paymentMethods','suppliers'));
        }
        if($request->type == 'supplier') {
            $selected_supplier = $request->filled('supplier_id') ? Supplier::companies()->find($request->supplier_id) : null;
            return view('admin.due.supplier', compact('accounts', 'paymentMethods','suppliers', 'selected_supplier'));
        }
        abort(404);
    }

    public function supplierwiseDueStore(DueRequest $request)
    {
        // Inject type so DueRequest@persist treats it as a customer payment
        // and creates the AccountReview and Transaction records for the ledger
        $request->merge(['type' => 'customer']);
        $request->persist();

        $record = CustomerSupplier::where('customer_id', $request->id)->where('supplier_id', $request->supplier_id)->first();
        if ($record) {
            CustomerSupplier::where('customer_id', $request->id)->where('supplier_id', $request->supplier_id)->update(['balance' => $request->payable_dueAmount]);
        } else {
            CustomerSupplier::create([
                'customer_id' => $request->id,
                'supplier_id' => $request->supplier_id,
                'balance' => $request->payable_dueAmount,
                'opening_balance' => 0
            ]);
        }

        (new SalesService())->updateCustomerBalance($request->id);

        $this->sendCollectionSms($request);

        $message = 'Customer due payment submitted successfully';
        return back()->withSuccess($message);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(DueRequest $request)
    {

    // dd($request->all());

        $request->persist();

        $message = 'Transaction Waiting for Approval Successful!';

        if ($request->type == 'customer') {
            $message = 'Customer Payment Successfully';

            (new SalesService())->updateCustomerBalance($request->id);
            $this->sendCollectionSms($request);
        } else if ($request->type == 'supplier') {
            $isReceive = ($request->payment_type == 'receive' || $request->action == 'receive');
            $message = $isReceive ? 'Supplier Money Received Successfully!' : 'Supplier Payment Successfully!';
            (new PurchaseService())->updateSupplierBalance($request->id);

        }
        return back()->withSuccess($message);
    }

    public function approve(DueRequest $request, AccountReview $review)
    {
        $request->approve($review);
        return back()->withSuccess('Transaction Successful!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\View\View
     */
    public function show(AccountReview $dueCollection)
    {
        $company = auth()->user()->company;
        return view('admin.due.show', compact('dueCollection', 'company'));
    }


    public function edit($id) {
        // $dueCollection = AccountReview::findOrFail($id);
        // $accounts = Account::get();
        // return view('admin.due.supplier-edit', compact('dueCollection', 'accounts'));
    }

    public function destroy(AccountReview $customerCollection)
    {
        $is_supplier = $customerCollection->transactionable_type == 'App\Supplier';
        $customer_id = $customerCollection->transactionable_id;
        $supplier_id = $customerCollection->fk_supplier_id;
        $amount      = $customerCollection->amount;

        $customerCollection->delete();

        if ($is_supplier) {
            // update suplier balance
            (new \App\Services\PurchaseService())->updateSupplierBalance($customer_id);
        } else {
            if ($supplier_id) {
                // Deleting a payment means we increase the due
                (new \App\Services\SalesService())->updateCustomerSupplierBalanceByDelta($customer_id, $supplier_id, $amount);
            }
            // update customer balance
            (new \App\Services\SalesService())->updateCustomerBalance($customer_id);
        }

        return back()->withSuccess('Transaction Deleted Successfully');
    }

    private function sendCollectionSms($request)
    {
        if ($request->filled('is_send_message') && $request->type == 'customer') {
            $customer = \App\Customer::find($request->id);
            if (!$customer) return;

            $company_name = optional(auth()->user()->company)->name ?? 'কোম্পানি';
            $date = $request->date ? date('d/m/Y', strtotime($request->date)) : date('d/m/Y');
            
            $previous_due = round((float) $request->previous_balance);
            $received_amount = round((float) $request->amount);
            $current_due = round((float) $customer->current_balance);

            $text = "প্রিয় গ্রাহক,\n {$date} তারিখে {$customer->name}-এর পক্ষ থেকে ৳{$received_amount} টাকা গ্রহণ করা হয়েছে।\n";
            
            if ($request->filled('supplier_id')) {
                $supplier = \App\Supplier::find($request->supplier_id);
                if ($supplier) {
                    $text .= "সাপ্লায়ার: {$supplier->name}\n";
                }
            }

            if ($previous_due >= 1) {
                $text .= "পূর্বের বকেয়া: ৳{$previous_due}\n";
            }
            $text .= "গৃহীত টাকা: ৳{$received_amount}\n";
            $text .= "বর্তমান বকেয়া: ৳{$current_due}\n";
            $text .= "ধন্যবাদান্তে,\n {$company_name}";

            $request['numbers'] = getValidMobileNumber($customer->phone);
            $request['text']    = $text;

            (new \App\Http\Controllers\SmsSendController())->sendSmsViaHttpGuzzle($request);
        }
    }

    public function fixDueBug()
    {
        $sales = \App\Sale::with('sale_details')->get();
        foreach ($sales as $sale) {
            $total_sales_amount = $sale->sale_details->sum(function ($item) {
                $base = $item->quantity * $item->unit_price;
                $discount_amount = $base * (floatval($item->product_discount) / 100);
                return $base - $discount_amount;
            });
            if ($sale->discount_type == 'flat') {
                $first_discount = $sale->invoice_discount;
            } else {
                $first_discount = $total_sales_amount * ($sale->invoice_discount / 100);
            }
            $remaining = $total_sales_amount - $first_discount;
            if ($sale->special_discount_type == 'flat') {
                $special_discount = $sale->special_discount;
            } else {
                $special_discount = $remaining * ($sale->special_discount / 100);
            }
            $courier_charge = 0;
            if ($sale->delivery_charge_payer == 'customer') {
                $courier_charge = floatval($sale->currier_amount ?? $sale->delivery_charge ?? 0);
            }
            $total_payable = $total_sales_amount + $sale->invoice_tax + $sale->cod_amount + $courier_charge - $first_discount - $special_discount;
            $receive_amount = floatval($sale->receive_amount);
            $change_amount  = floatval($sale->change_amount ?? 0);
            $net_paid       = $receive_amount - $change_amount;
            $previous_due   = floatval($sale->previous_due);
            $advanced       = floatval($sale->advanced);
            $balance = ($previous_due + $total_payable) - ($net_paid + $advanced);
            $sale->update([
                'sub_total'       => $total_sales_amount,
                'total_payable'   => $total_payable,
                'balance'         => $balance,
            ]);
        }
        $service = new \App\Services\Reports\CustomerReportServices();
        $customerSuppliers = \App\Models\CustomerSupplier::all();
        foreach ($customerSuppliers as $cs) {
            $customer = \App\Customer::find($cs->customer_id);
            if ($customer) {
                $customerBalances = (object)$service->getCustomersBalances(collect([$customer]), null, $cs->supplier_id);
                $correct_balance = $customerBalances->sum('balance');
                \App\Models\CustomerSupplier::where('customer_id', $cs->customer_id)
                    ->where('supplier_id', $cs->supplier_id)
                    ->update(['balance' => $correct_balance]);
            }
        }
        $customers = \App\Customer::all();
        foreach ($customers as $customer) {
            $customerBalances = (object)$service->getCustomersBalances(collect([$customer]));
            $correct_balance = $customerBalances->sum('balance');
            \App\Customer::where('id', $customer->id)->update(['current_balance' => $correct_balance]);
        }
        return redirect()->back()->withSuccess('All corrupted balances have been successfully recalculated and fixed.');
    }
}
