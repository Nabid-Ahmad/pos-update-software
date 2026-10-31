<?php
 
namespace App\Http\Controllers\Reports;
 
use App\Customer;
use App\Account;
use App\PaymentMethod;
use App\Services\Reports\CashFlowReportService;
use App\Services\Reports\CustomerReportServices;
use App\Services\Reports\SupplierReportServices;
use App\Http\Controllers\Controller;
use App\Exports\ReportExport;
use Illuminate\Http\Request;
use App\Supplier;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
 
class FinancialReportController extends Controller
{
    public $today;

    public function __construct()
    {
        $this->today                = Carbon::now()->format('Y-m-d');
    }

    public function cashFlowReport(Request $request)
    {
        $data = (new CashFlowReportService())->getCashFlowReportData($request);
        if ($request->print == 'excel') {
            $data['page_name'] = 'cash-flow-reports';
            return Excel::download(new ReportExport($data), ('CashFlowReport-'.$this->today.'.xlsx'));
        }
        return view('admin.reports.cash_reports', $data);
    }

    //  ###########################################################     customer report  ################################################
    public function customerReports(Request $request)
    {
        $service    = new CustomerReportServices();

        $data['transactions']   = $service->getAllCustomerReportData();
        $data['openingBalance'] = $service->getOpeningBalanceFromDate();
        $data['customer']       = $service->getCustomer();
        $data['customers']      = Customer::companies()->get()->mapWithKeys(function ($c) {
            return [$c->id => $c->name . ' (' . $c->customerId . ')'];
        });
        $data['suppliers']      = \App\Supplier::companies()->pluck('name', 'id');
        // return $data['transactions'];
        if($request->print == 'excel')
        {
            $data['page_name'] = 'customer-reports';
            return Excel::download(new ReportExport($data), ('CustomerReport-'.$this->today.'.xlsx'));
        }

        return view('admin.reports.financial-reports.customer-reports', $data);
    }


    //  ###########################################################     supplier report  ################################################
    public function supplierReports(Request $request)
    {
        $service = new SupplierReportServices();

        $data['transactions']   = $service->getAllSupplierReportData();
        // dd($data);

        $data['openingBalance'] = $service->getOpeningBalanceFromDate();
        $data['supplier']       = $service->getSupplier();
        $data['suppliers']      = Supplier::suppliers()->pluck('name', 'id');

        if($request->print == 'excel')
        {
            $data['page_name'] = 'supplier-reports';
            return Excel::download(new ReportExport($data), ('SupplierReport-'.$this->today.'.xlsx'));
        }

        return view('admin.reports.financial-reports.supplier-reports', $data);
    }

    public function receivableDueReport(Request $request)
    {
        $selectCustomers = Customer::companies()->orderBy('name')->pluck('name', 'id');
        $selectSuppliers = Supplier::companies()->orderBy('name')->pluck('name', 'id');

        $query = Customer::companies()->orderBy('name');

        if ($request->filled('fk_customer_id')) {
            $query->where('id', $request->fk_customer_id);
        }

        if ($request->filled('fk_supplier_id')) {
            $query->whereExists(function ($q) use ($request) {
                $q->select('customer_id')
                  ->from('customer_suppliers')
                  ->whereColumn('customer_suppliers.customer_id', 'customers.id')
                  ->where('customer_suppliers.supplier_id', $request->fk_supplier_id);
            });
        }

        // Add subquery for last sale date
        $query->select('customers.*')
            ->selectSub(function($q) {
                $q->select('sale_date')
                    ->from('sales')
                    ->whereColumn('fk_customer_id', 'customers.id')
                    ->latest('sale_date')
                    ->limit(1);
            }, 'last_sale_date');

        // Apply date range filters if present
        if ($request->filled('from') || $request->filled('to')) {
            $query->whereHas('sale', function ($q) use ($request) {
                if ($request->filled('from')) {
                    $q->where('sale_date', '>=', $request->from);
                }
                if ($request->filled('to')) {
                    $q->where('sale_date', '<=', $request->to);
                }
            });
        }

        $customers = $query->get();

        $service = new CustomerReportServices();

        // Calculate balances dynamically up to the selected 'to' date (End Date)
        $toDate = $request->filled('to') ? $request->to : null;
        $supplierId = $request->filled('fk_supplier_id') ? $request->fk_supplier_id : null;
        $customerBalances = (object)$service->getCustomersBalances($customers, $toDate, $supplierId);

        // Map calculated balances back to the customer objects for display
        foreach ($customers as $customer) {
            $balanceObj = collect($customerBalances)->firstWhere('id', $customer->id);
            if ($balanceObj) {
                $customer->current_balance = $balanceObj->balance;
            }

            // If a specific supplier is selected, show only that supplier's opening balance
            if ($supplierId) {
                $cs = \App\Models\CustomerSupplier::where('customer_id', $customer->id)
                        ->where('supplier_id', $supplierId)
                        ->first();
                $customer->customer_previous_due = optional($cs)->opening_balance ?? 0;
            }
        }

        if ($request->type == 'update') {
            foreach ($customerBalances as $key => $customerBal) {
                Customer::find($customerBal->id)->update(['current_balance' => $customerBal->balance]);
            }
            
            $updateQuery = Customer::companies()->orderBy('name');
            $updateQuery->select('customers.*')
                ->selectSub(function($q) {
                    $q->select('sale_date')
                        ->from('sales')
                        ->whereColumn('fk_customer_id', 'customers.id')
                        ->latest('sale_date')
                        ->limit(1);
                }, 'last_sale_date');

            if ($request->filled('fk_customer_id')) {
                $updateQuery->where('id', $request->fk_customer_id);
            }
            if ($request->filled('fk_supplier_id')) {
                $updateQuery->whereExists(function ($q) use ($request) {
                    $q->select('customer_id')
                      ->from('customer_suppliers')
                      ->whereColumn('customer_suppliers.customer_id', 'customers.id')
                      ->where('customer_suppliers.supplier_id', $request->fk_supplier_id);
                });
            }
            if ($request->filled('from') || $request->filled('to')) {
                $updateQuery->whereHas('sale', function ($q) use ($request) {
                    if ($request->filled('from')) {
                        $q->where('sale_date', '>=', $request->from);
                    }
                    if ($request->filled('to')) {
                        $q->where('sale_date', '<=', $request->to);
                    }
                });
            }
            $customers = $updateQuery->get();
        }

        $accounts       = Account::getAccountWithBalance();
        $paymentMethods = PaymentMethod::companies()->where('status', 1)->pluck('method_name', 'id');

        return view('admin.reports.financial-reports.receivable-due-reports', compact('customerBalances', 'customers', 'selectCustomers', 'selectSuppliers', 'accounts', 'paymentMethods'));
    }

    public function getPayableDueReport(Request $request)
    {
        $service    = new SupplierReportServices();
        $suppliers = $service->getSuppliersBalances(Supplier::companies()->get());

        $supplierBalances = (object)$suppliers;


        if ($request->type == 'update') {
            foreach ($supplierBalances as $key => $supplier) {
                Supplier::find($supplier->id)->update(['current_balance' => $supplier->balance]);
            }
        }

        $suppliers = Supplier::companies()->get();
        return view('admin.reports.financial-reports.payable-due-reports', compact('supplierBalances', 'suppliers'));
    }
}
