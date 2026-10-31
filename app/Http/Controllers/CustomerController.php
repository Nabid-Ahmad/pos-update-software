<?php

namespace App\Http\Controllers;

use App\CustomerCategory;
use App\CustomerUpload;
use App\Imports\ImportCustomerCSV;
use App\Services\SalesService;
use App\Traits\FileSaver;
use App\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;

class CustomerController extends Controller
{
    use FileSaver;

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
       $customers = Customer::customers()
           ->when($request->filled('search'), function ($q) use ($request) {
               $termNumber = preg_replace('/[^0-9]/', '', $request->search);
               $q->where(function ($query) use ($request, $termNumber) {
                   $query->where('name', 'LIKE', "%{$request->search}%")
                   ->orWhere('customer_code', 'LIKE', "%{$request->search}%")
                   ->orWhere('phone', 'LIKE', "%{$request->search}%")
                   ->orWhere('address', 'LIKE', "%{$request->search}%")
                   ->orWhere('email', 'LIKE', "%{$request->search}%");
                   if (!empty($termNumber)) {
                       $query->orWhere('id', '=', (int)$termNumber);
                   }
               });
           })
           ->orderBy('name');
       if ($request->ajax()) {
           $termNumber = preg_replace('/[^0-9]/', '', $request->name);
           return $customers->where(function ($q) use ($request, $termNumber) {
               $q->where('name', 'LIKE', "%{$request->name}%")
                 ->orWhere('customer_code', 'LIKE', "%{$request->name}%");
               if (!empty($termNumber)) {
                   $q->orWhere('id', '=', (int)$termNumber);
               }
           })->take(15)->get()->mapWithKeys(function ($item) {
               return [$item->id => $item->customerId . ' - ' . $item->name];
           });
       }

       return view('admin.people.customers.index', ['customers' => $customers->userLog()->paginate(30)]);
    }
    public function create()
    {
        $customer_categories = CustomerCategory::companies()->get();
        $suppliers = \App\Supplier::companies()->get();
       return view('admin.people.customers.create', compact('customer_categories', 'suppliers'));
    }


    public function store(Request $request)
    {
        // validate customer data
        $request->validate([
            'name'                  => 'required|string|max:160',
            'phone'                 => 'required|string|max:20|unique:customers,phone,NULL,id,deleted_at,NULL,fk_company_id,' . companyId(),
            'email'                 => 'nullable|string|email|max:160|unique:customers,email,NULL,id,deleted_at,NULL,fk_company_id,' . companyId(),
            'customer_previous_due' => 'nullable|numeric|min:0',
        ]);
        try {
            DB::transaction(function () use($request) {
                // new customer create
                $data = $request->except('image');
                $data['customer_previous_due'] = $request->customer_previous_due ?? 0;
                $customer = Customer::create($data);

                // upload customer image
                $this->uploadFileWithResize($request->image, $customer, 'image', 'uploads/customers');

                // set customer as default
                $this->setDefaultCustomer($request, $customer->id);

                $customer->update(['current_balance' => $customer->customer_previous_due]);

                // Set supplier-wise opening balances
                if ($request->has('supplier_amounts')) {
                    $supplier_dues = [];
                    foreach($request->supplier_amounts as $index => $amount) {
                        $amount = floatval($amount);
                        if ($amount > 0) {
                            $sup_id = $request->supplier_ids[$index] ?? null;
                            if ($sup_id) {
                                if (isset($supplier_dues[$sup_id])) {
                                    $supplier_dues[$sup_id] += $amount;
                                } else {
                                    $supplier_dues[$sup_id] = $amount;
                                }
                            }
                        }
                    }
                    foreach($supplier_dues as $sup_id => $amt) {
                        \App\Models\CustomerSupplier::create([
                            'customer_id'     => $customer->id,
                            'supplier_id'     => $sup_id,
                            'balance'         => $amt,
                            'opening_balance' => $amt
                        ]);
                    }
                }
            });
            return redirect()->route('customers.index')->withSuccess('Customer Created Successfully!');
        } catch (\Exception $ex) {
            return redirect()->back()->withError($ex->getMessage());
        }
    }




    public function show($id)
    {
        $customer = $this->get_customer($id);
        return view('admin.people.customers.show', ['customer'=>$customer]);
    }


    public function edit(Customer $customer)
    {
        $customer_categories = CustomerCategory::companies()->get();
        $suppliers = \App\Supplier::companies()->get();
        $customer_suppliers = \App\Models\CustomerSupplier::where('customer_id', $customer->id)->get();

        return view('admin.people.customers.edit', [
            'customer' => $customer,
            'customer_categories' => $customer_categories,
            'suppliers' => $suppliers,
            'customer_suppliers' => $customer_suppliers
        ]);
    }


    public function update(Request $request, Customer $customer)
    {
        $this->validate($request, [
            'name'  => 'required|max:160|unique:customers,name,'.$customer->id.',id,deleted_at,NULL,fk_company_id,' . companyId(),
            'phone' => 'nullable|unique:customers,phone,' . $customer->id . ',id,deleted_at,NULL,fk_company_id,' . companyId(),
            'email' => 'nullable|max:160|unique:customers,email,' . $customer->id . ',id,deleted_at,NULL,fk_company_id,' . companyId(),
        ]);

        $data = $request->only('name', 'phone', 'email', 'address', 'customer_category_id', 'due_limit', 'company_name');
        
        $total_ob = 0;
        if ($request->has('supplier_amounts')) {
            foreach ($request->supplier_amounts as $amt) {
                $total_ob += floatval($amt);
            }
        }
        $data['customer_previous_due'] = $total_ob;

        try {
            // update default customer
            $this->setDefaultCustomer($request, $customer->id);
            $data['default'] = $request->has('default') ? 1 : 0;

            // update customer info
            $customer->update($data);
            
            // Re-sync supplier-wise opening balances with delta calculation
            if ($request->has('supplier_amounts')) {
                // 1. Aggregate new opening balances
                $new_supplier_dues = [];
                foreach($request->supplier_amounts as $index => $amount) {
                    $amount = floatval($amount);
                    if ($amount > 0) {
                        $sup_id = $request->supplier_ids[$index] ?? null;
                        if ($sup_id) {
                            if (isset($new_supplier_dues[$sup_id])) {
                                $new_supplier_dues[$sup_id] += $amount;
                            } else {
                                $new_supplier_dues[$sup_id] = $amount;
                            }
                        }
                    }
                }
                
                // 2. Fetch existing records
                $existing_records = \App\Models\CustomerSupplier::where('customer_id', $customer->id)->get();
                $existing_sup_ids = [];
                
                foreach($existing_records as $record) {
                    $existing_sup_ids[] = $record->supplier_id;
                    $old_ob = (float)($record->opening_balance ?? 0);
                    
                    // If this supplier is still in the new submission
                    if (isset($new_supplier_dues[$record->supplier_id])) {
                        $new_ob = $new_supplier_dues[$record->supplier_id];
                        $delta = $new_ob - $old_ob;
                        
                        \App\Models\CustomerSupplier::where('customer_id', $customer->id)
                            ->where('supplier_id', $record->supplier_id)
                            ->update([
                                'balance' => (float)$record->balance + $delta,
                                'opening_balance' => $new_ob
                            ]);
                    } else {
                        // Supplier removed from opening balance table
                        $delta = 0 - $old_ob;
                        $new_balance = (float)$record->balance + $delta;
                        
                        // If totally zero, we can optionally delete, but to be safe we just save 0
                        if ($new_balance == 0 && $record->due_limit == null) {
                            \App\Models\CustomerSupplier::where('customer_id', $customer->id)
                                ->where('supplier_id', $record->supplier_id)
                                ->delete();
                        } else {
                            \App\Models\CustomerSupplier::where('customer_id', $customer->id)
                                ->where('supplier_id', $record->supplier_id)
                                ->update([
                                    'balance' => $new_balance,
                                    'opening_balance' => 0
                                ]);
                        }
                    }
                }
                
                // 3. Insert brand new records
                foreach($new_supplier_dues as $sup_id => $amt) {
                    if (!in_array($sup_id, $existing_sup_ids)) {
                        \App\Models\CustomerSupplier::create([
                            'customer_id'     => $customer->id,
                            'supplier_id'     => $sup_id,
                            'balance'         => $amt,
                            'opening_balance' => $amt
                        ]);
                    }
                }
            }

            // upload customer image
            $this->uploadFileWithResize($request->image, $customer, 'image', 'uploads/customers');

            // update customer balance
            (new SalesService)->updateCustomerBalance($customer->id);

            return redirect()->route('customers.index')->withSuccess('Customer Updated Successfully!');
        } catch (\Exception $ex) {
            return redirect()->back()->withError($ex->getMessage());
        }
    }

    public function setDefaultCustomer(Request $request, $id)
    {
        return $request->has('default')
            ?  Customer::where('id', '<>', $id)->update(['default' => false])
            : false;
    }


    public function destroy(Customer $customer)
    {
        try {
            if ($customer->id == 1){
                return redirect()->back()->with('error_message','This default Customer cannot be deleted.');
            }
            $customer->delete();
        } catch (\Exception $ex) {
            return redirect()->back()->with('error_message', $ex->getMessage());
        }

        return \redirect('people/customers')->with(['message'=>'Customer Deleted Successfully!']);
    }

    public function get_customer($id){
        return Customer::findOrFail($id);
    }



    // upload customers
    public function uploadCustomers()
    {
        return view('admin.people.customers.uploads');
    }

    public function uploadCustomersStore(Request $request)
    {

        if ($request->file('customer_csv')) {
            try {
                $data = Excel::import(new ImportCustomerCSV(), $request->file('customer_csv'));
            } catch (Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
            return redirect()->route('customer-confirm-list.index')->with('message', 'File Uploaded Successfully')->withSuccess('File Uploaded Successfully');
        } else {
            return redirect()->back();
        }
    }

    public function customerConfirmList()
    {
        $customers = CustomerUpload::where('fk_company_id', companyId())->orderBy('id')->paginate(50);
        return view('admin.people.customers.confirm-list', compact('customers'));
    }

    public function customerConfirmListStore(Request $request)
    {
        $log            = $this->getUserLog();
        $customerIds    = [];
        $customers      = CustomerUpload::where('fk_company_id', companyId())->take(50)->get();

        foreach ($customers as $key => $customer) {
            $item = array_merge($log, [
                'customer_code'         => $customer_code = 'customer-' . (Customer::withTrashed()->max('id') + 1),
                'name'                  => $customer->name,
                'email'                 => $customer->email,
                'phone'                 => $customer->phone,
                'address'               => $customer->address,
                'due_limit'             => $customer->due_limit ?? 999999999,
                'customer_category_id'  => $this->getCustomerCategoryId($customer->customer_category),
                'customer_previous_due' => $customer->opening_due ?? 0,
            ]);

            try {
                $newCustomer = Customer::create($item);
                $newCustomer->update(['current_balance' => $newCustomer->customer_previous_due]);

                array_push($customerIds, $customer->id);
            } catch (\Exception $ex) {}
        }
        if (count($customerIds) > 0) {
            CustomerUpload::destroy($customerIds);
            return redirect()->back()->withSucceess('Customer upload confirm');
        } else {
            return redirect()->back()->withErrors('No customer uploaded, something error');
        }
    }

    private function getCustomerCategoryId($category_name)
    {
        $category = CustomerCategory::where('company_id', companyId())->where(function($q) use ($category_name) {
            $q->where('name', $category_name)->orWhere('id', $category_name);
        })->first();
        return $category_id  = optional($category)->id ?? CustomerCategory::create([
                'company_id' => companyId(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'name' => $category_name
            ])->id;
    }

    private function getUserLog()
    {
        return [
            'fk_company_id' => companyId(),
            'fk_created_by' => auth()->id(),
            'fk_updated_by' => auth()->id(),
        ];
    }

    public function confirmCustomerDelete($id)
    {
        try {
            CustomerUpload::destroy($id);
            return redirect()->back()->withSuccess('Customer Deleted Successfully!');
        }catch (\Exception $ex){
            return redirect()->back()->withError($ex->getMessage());
        }
    }

    public function confirmCustomerDeleteAll()
    {
        try {
            CustomerUpload::where('fk_company_id', companyId())->delete();
            return redirect()->back()->withSuccess('All Customer Deleted Successfully!');
        }catch (\Exception $ex){
            return redirect()->back()->withError($ex->getMessage());
        }
    }

    public function getLastAddedCustomer()
    {
        $customer = Customer::with('customer_category')->companies()->latest()->first();
        if ($customer) {
            $areaName = optional($customer->customer_category)->name;
            $customer->area_name = $areaName;
            $customer->formatted_name = $customer->name . ($customer->phone ? ' - ' . $customer->phone : '') . ($areaName ? ' - ' . $areaName : '');
        }
        return $customer;
    }
}
