<?php

namespace App\Http\Controllers;

use App\{CustomerCategory, CustomerPricing, SoftwareSetting, AccountReview, ProductStock, Transaction, Warehouse, Customer, Account, Product, Company, Currier, marketer, marketers_details, marketing, ProductSerial, Status, Sale};
use App\Http\Requests\{SaleUpdateRequest, SaleRequest};
use App\Mail\MailSender;
use App\Models\CustomerSupplier;
use App\Models\RandomReturn;
use App\ProductSerialSalesDetails;
use App\Services\SalesService;
use App\Services\Reports\CustomerReportServices;
use App\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Mail, DB};

class SaleController extends Controller
{

    public $balance = 0;
    public function __construct()
    {
        parent::__construct();
    }



    public function index(Request $request)
    {
        $invoiceId = (int)str_ireplace('#S-', '', $request->search);
        $data['customers'] = Customer::companies()->get()->mapWithKeys(function ($c) {
            return [$c->id => $c->name . ' (' . $c->customerId . ')'];
        });
        $softwareSetting = SoftwareSetting::get();

        $data['isSendSms'] = $softwareSetting->where('title', 'Send Invoice Sms To Customer')->where('options', 'yes')->count() ? true : false;
        $data['isSendMail'] = $softwareSetting->where('title', 'Send Invoice Mail To Customer')->where('options', 'yes')->count() ? true : false;
        $data['isSendSms'] = true;
        $data['sales'] = Sale::companies()->userLog()->with('sale_customer:name,id,phone', 'marketers')
            ->with(['sale_details' => function ($q) {
                $q->select('id', 'fk_sales_id')->withCount('serials');
            }])
            ->latest()
            ->when($request->filled('search'), function ($q) use ($request, $invoiceId) {
                $customerId = (int)str_ireplace(['#c-', 'c-', '#C-', 'C-'], '', $request->search);
                $q->where('id', 'LIKE', "{$invoiceId}%")
                    ->orWhere('sale_invoice_id', 'LIKE', "{$invoiceId}%")
                    ->orWhere('sale_date', $request->search)
                    ->orWhere('fk_customer_id', $customerId);
            })->when($request->filled('customer_id'), function ($q) use ($request) {
                $q->where('fk_customer_id', $request->customer_id);
            })->when($request->filled('start_date'), function ($q) use ($request) {
                $q->whereDate('sale_date', '>=', $request->start_date);
            })->when($request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('sale_date', '<=', $request->end_date);
            })->when($request->filled('invoice_id'), function ($q) use ($request) {
                $invoice_id = $request->invoice_id;
                $clean_invoice_id = (int)str_ireplace('#S-', '', $invoice_id);

                $q->where('id', $clean_invoice_id)
                    ->orWhere('sale_invoice_id', $clean_invoice_id);
            })->withCount(['sale_details as total_sale_amount' => function ($q) {
                $q->select(DB::Raw('SUM(product_sub_total)'));
            }])->paginate(30);
        return view('admin.sales.index', $data);
    }
    public function create(Request $request)
    {
        $defaultWarehouseId = Warehouse::companies()->first()->id ?? 1;
        $warehouseId = $request->warehouse_id ?? $defaultWarehouseId;

        $data['marketers']          = marketer::companies()->get();
        $data['statuses']           = Status::all();
        $data['settings']           = SoftwareSetting::companies()->get();
        $data['couriers']           = Currier::companies()->orderBy('name')->pluck('name', 'id');
        $data['suppliers']          = Supplier::companies()->orderBy('name')->get(['id', 'name', 'phone'])->mapWithKeys(function($supplier) {
            return [$supplier->id => $supplier->name . ($supplier->phone ? ' - ' . $supplier->phone : '')];
        })->toArray();
        $data['categories']         = CustomerCategory::companies()->get();
        $data['warehouses']         = Warehouse::companies()->pluck('name', 'id');
        $data['account_infos']      = Account::getAccountWithBalance();
        $data['customers']          = Customer::companies()->orderBy('name')->select('name', 'phone', 'due_limit', 'current_balance as balance', 'default as is_default', 'id', 'customer_category_id')->get();
        $data['products']           = Product::companies()->with('barcode')->select('id', 'product_code', 'product_name', 'product_cost', 'product_price', 'has_serial', 'tax', 'supplier_id')
            ->withCount(['warehouse_stocks as available_quantity' => function ($q) use ($request, $warehouseId) {
                if ($request->filled('warehouse_id')) {
                    $q->where('warehouse_id', $warehouseId);
                }
                $q->select(DB::Raw('SUM(available_quantity)'));
            }])->get();

        $data['recent_sales']       = Sale::companies()->with('sale_customer')->latest()->take(5)->get();

            // dd($data['customers']);
        if ($request->type == 'hole-sale') {
            return view('admin.sales.holesale.hole-sale-create', $data);
        }
        return view('admin.sales.create', $data);
    }

    public function newSaleCreate(Request $request)
    {
        $defaultWarehouseId = Warehouse::companies()->first()->id ?? 1;
        $warehouseId = $request->warehouse_id ?? $defaultWarehouseId;

        // dd($request->all());
        $data['marketers']          = marketer::companies()->get();
        $data['statuses']           = Status::all();
        $data['settings']           = SoftwareSetting::companies()->get();
        $data['couriers']           = Currier::companies()->orderBy('name')->pluck('name', 'id');
        $data['suppliers']          = Supplier::companies()->orderBy('name')->get(['id', 'name', 'phone'])->mapWithKeys(function($supplier) {
            return [$supplier->id => $supplier->name . ($supplier->phone ? ' - ' . $supplier->phone : '')];
        })->toArray();
        $data['categories']         = CustomerCategory::companies()->get();
        $data['warehouses']         = Warehouse::companies()->pluck('name', 'id');
        $data['account_infos']      = Account::getAccountWithBalance();
        $data['customers']          = Customer::companies()->orderBy('name')->select('name', 'phone', 'due_limit', 'current_balance as balance', 'default as is_default', 'id', 'customer_category_id')->get();
        $data['products']           = Product::companies()->with('barcode')->select('id', 'product_code', 'product_name', 'product_cost', 'product_price', 'has_serial', 'tax', 'supplier_id')
            ->withCount(['warehouse_stocks as available_quantity' => function ($q) use ($request, $warehouseId) {
                if ($request->filled('warehouse_id')) {
                    $q->where('warehouse_id', $warehouseId);
                }
                $q->select(DB::Raw('SUM(available_quantity)'));
            }])->get();

        $data['recent_sales']       = Sale::companies()->with('sale_customer')->latest()->take(5)->get();

        if ($request->type == 'hole-sale') {
            return view('admin.sales.holesale.hole-sale-create', $data);
        }
        return view('admin.sales.create-new-sale', $data);
    }

    private function getDefaultCustomer()
    {
        return $defaultCustomer = Customer::companies()
            ->where('default', true)
            ->select('id', 'name')
            ->first();
    }

    public function store(SaleRequest $request)
    {
        $sale = $request->storeSale();

        if ($request->filled('is_send_message')) {
            $this->sendSms($sale, $request);
        }
        
        if ($request->has('send_email_invoice')) {
            session()->flash('send_invoice_email_sale_id', $sale->id);
        }

        if ($request->print_type == 'pos') {
            $url = route('sales.show', $sale->id) . '?print_type=pos-invoice';
        } else {
            $url = route('sales.show-v2', $sale->id) . '?print_type=invoice';
        }
        return redirect($url)->withSuccess('Sale updated successfully!');
    }

    public function newSaleStore(SaleRequest $request)
    {
        $sale = $request->storeSaleSupplierWise();

        if ($request->filled('is_send_message')) {
            $this->sendSms($sale, $request);
        }

        if ($request->has('send_email_invoice')) {
            session()->flash('send_invoice_email_sale_id', $sale->id);
        }

        if ($request->print_type == 'pos') {
            $url = route('sales.show', $sale->id) . '?print_type=pos-invoice';
        } else {
            $url = route('sales.show-v2', $sale->id) . '?print_type=invoice';
        }
        return redirect($url)->withSuccess('Sale updated successfully!');
    }

    public function sendInvoiceEmailAjax(Request $request)
    {
        $saleId = $request->sale_id;
        if (!$saleId) return response()->json(['success' => false, 'message' => 'No sale ID provided']);

        $sale = Sale::with('sale_customer', 'sale_details.product')->find($saleId);
        if (!$sale || !$sale->sale_customer || !$sale->sale_customer->email) {
            return response()->json(['success' => false, 'message' => 'Customer email not found']);
        }

        $mailSetting = \App\MailSetting::companies()->first();
        if (!$mailSetting || !$mailSetting->mail_host) {
            return response()->json(['success' => false, 'message' => 'Mail settings not configured']);
        }

        try {
            config(['mail.mailer' => $mailSetting->mail_mailer]);
            config(['mail.host' => $mailSetting->mail_host]);
            config(['mail.port' => $mailSetting->mail_port]);
            config(['mail.username' => $mailSetting->mail_username]);
            config(['mail.password' => $mailSetting->mail_password]);
            config(['mail.encryption' => $mailSetting->mail_encryption]);
            config(['mail.from.address' => $mailSetting->mail_from_address]);
            config(['mail.from.name' => $mailSetting->mail_from_name]);

            $company = \App\Company::find(companyId());

            \Illuminate\Support\Facades\Mail::to($sale->sale_customer->email)
                ->send(new \App\Mail\InvoiceMail($sale, $company));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function show(Request $request, $id)
    {
        $type = $request->print_type = $request->print_type == 'pos-invoice' ? 'pos-invoice' : 'invoice';

        try {
            $data['settings']   = SoftwareSetting::companies()->get();
            $data['sale']       = Sale::with(['sale_customer'])->withCount(['sale_details as total_sale_amount' => function ($q) {
                $q->select(DB::Raw('SUM(product_sub_total)'));
            }])->with(['sale_details' => function ($q) {
                $q->with('product', 'package');
            }])->find($id);
            return view('admin.sales.' . $type, $data);
        } catch (\Exception $ex) {
            return $ex->getMessage();
            return back()->withError($ex->getMessage());
        }
    }

    public function showV2(Request $request, $id)
    {
        $type = 'invoice';

        try {
            $data['settings']   = SoftwareSetting::companies()->get();
            $data['sale']       = Sale::with(['sale_customer'])->withCount(['sale_details as total_sale_amount' => function ($q) {
                $q->select(DB::Raw('SUM(product_sub_total)'));
            }])->with(['sale_details' => function ($q) {
                $q->with('product', 'package');
            }])->find($id);
            return view('admin.sales.invoice-v2', $data);
        } catch (\Exception $ex) {
            return $ex->getMessage();
            return back()->withError($ex->getMessage());
        }
    }

    
    public function edit(Sale $sale)
    {
        $data['sale']               = $sale;
        $data['marketers']          = marketer::companies()->get();
        $data['statuses']           = Status::all();
        $data['defaultCustomer']    = $this->getDefaultCustomer();
        $data['categories']         = CustomerCategory::companies()->get();
        $data['account_infos']      = Account::getAccountWithBalance();
        $data['couriers']           = Currier::companies()->orderBy('name')->pluck('name', 'id');
        $data['warehouses']         = Warehouse::companies()->pluck('name', 'id');
        $data['customers']          = Customer::companies()->orderBy('name')->select('name', 'phone', 'due_limit', 'current_balance as balance', 'default as is_default', 'id', 'customer_category_id')->get();
        $data['settings']           = SoftwareSetting::companies()->get();
        $data['products']           = Product::companies()->get(['id', 'product_code', 'product_name', 'product_cost', 'product_price', 'has_serial', 'tax']);
        return view('admin.sales.edit', $data);

        // return view('admin.sales.edit-sale.edit', $data);
    }
    public function update(SaleUpdateRequest $request, Sale $sale)
    {
        try {
            $request->updateSale($sale);
            
            $url = route('sales.show-v2', $sale->id) . ($request->print_type ? '?print_type=pos-invoice' : '?print_type=invoice') . '&t=' . time();

            return redirect($url)->withSuccess('Sale updated successfully!');
        } catch (\Exception $e) {
            dd('ERROR DURING UPDATE: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    public function destroy(Sale $sale)
    {
        $customer_id = $sale->fk_customer_id;
        $supplier_id = $sale->supplier_id;
        $delta = -($sale->total_payable - $sale->paid_amount - $sale->advanced);
        
        if ($sale->returns()->isEmpty()) {
            try {
                DB::transaction(function () use ($sale) {
                    foreach ($sale->sale_details as $item) {
                        if ($item->product->has_serial == 1) {
                            ProductSerial::whereIn('id', $item->serials->pluck('id'))->update([
                                'is_sold' => 0
                            ]);
                            ProductSerialSalesDetails::where('sales_details_id', $item->id)->delete();
                        }
                        
                        $warehouseId = $sale->warehouse_id ?: optional(\App\Warehouse::companies()->first())->id ?: null;
                        $productStock = \App\ProductStock::where('fk_product_id', $item->fk_product_id)
                            ->where('fk_company_id', $sale->fk_company_id)
                            ->where('warehouse_id', $warehouseId)
                            ->first();
                            
                        if ($productStock) {
                            $productStock->update([
                                'sold_quantity' => $productStock->sold_quantity - $item->quantity,
                                'available_quantity' => $productStock->available_quantity + $item->quantity
                            ]);
                        }
                    }

                    $sale->payments()->delete();
                    try {
                        $sub_total = Sale::where('id', $sale->id)->first()->sub_total;
                        $marketers_details = marketers_details::where('start_amount', '<=', $sub_total)
                            ->where('end_amount', '>=', $sub_total)->first();
                        if($marketers_details)
                        {
                            $percantage = $marketers_details->marketers_commission;
                            $marketers_amount = round(($sub_total * $percantage) / 100);
                            $marketers = marketer::where('id', $sale->marketers_id);
                            if ($marketers->first() != null || $marketers->first() != '') {
                                $balance = $marketers->first()->balance;
                                $old_amount = $balance - $marketers_amount;
                                $marketers->update([
                                    'balance' => $old_amount
                                ]);
                            }
                        }
                    } catch (\Exception $ex) {
                        // Logic for failing silently or logging could go here
                    }
                    $sale->delete();
                });
            } catch (\Exception $ex) {
                return $ex->getMessage();
                return back()->withError($ex->getMessage());
            }
            // update customer balance
            (new SalesService())->updateCustomerBalance($customer_id);
            if ($supplier_id) {
                (new SalesService())->updateCustomerSupplierBalanceByDelta($customer_id, $supplier_id, $delta);
            }
            return \redirect('sales')->withSuccess('Sale Deleted Successfully!');
        }
        return \redirect('sales')->withError('Sale can not be deleted!');
    }




    public function available_quantity(Request $request, $product_id)
    {
        $product_stocks = ProductStock::companies()->with('stock_product')->where('fk_product_id', $product_id)->first() ?? [];
        return response()->json($product_stocks);
    }

    public function getProduct($product_id)
    {
        return response()->json(Product::find($product_id));
    }

    public function account_linked()
    {
        $account_linked = Company::findOrFail(Auth::user()->fk_company_id)->account_linked;
        return $account_linked;
    }

    public function searchProduct(Request $request)
    {
        $product = Product::with('product_stock')
            ->companies()
            ->where('product_name', 'LIKE', "%{$request->name}%")
            ->orWhere('product_code', 'LIKE', "%{$request->name}%")
            ->orWhereHas('barcode', function ($q) use ($request) {
                $q->where('barcode_number', $request->name);
            })
            ->selectRaw('product_name as name, product_code, id, product_price, tax, opening_quantity')
            ->take(20)->get()
            ->map(function ($product) {
                return [
                    'id'                => $product->id,
                    'name'              => $product->name,
                    'product_code'      => $product->product_code,
                    'product_price'     => $product->product_price,
                    'tax'               => $product->tax,
                    'retail_quantity'   => $product->product_stock->available_quantity ?? 0,
                ];
            });
        return response()->json($product);
    }

    public function searchProductByCustomerId(Request $request, $customer_id, $warehouse_id = null)
    {
        $product_like_search = Product::with('product_rak')
            ->with(['warehouse_stocks' => function ($q) use ($warehouse_id) {
                if ($warehouse_id && $warehouse_id != '0') {
                    $q->where('warehouse_id', $warehouse_id);
                }
                $q->where('fk_company_id', companyId());
            }])
            ->selectRaw('product_name as name, product_code, id, product_cost, product_price, tax, discount')
            ->where(function ($q) use ($request) {
                $q->where('product_name', 'LIKE', "%" . $request->name . "%")
                    ->orWhere('product_code', 'LIKE', "%" . $request->name . "%")
                    ->orWhereHas('barcode', function ($q) use ($request) {
                        $q->where('barcode_number', $request->name);
                    });
            })

            ->when($customer_id != null, function ($qr) use ($customer_id) {
                $qr->withCount(['pricing as customer_price' => function ($q) use ($customer_id) {
                    $q->where('customer_id', $customer_id)->select(DB::Raw('SUM(price)'));
                }]);
            })
            ->take(10)->get();

        $product = $product_like_search
            ->map(function ($product) {
                $stock = $product->warehouse_stocks->first();

                return [
                    'id'                => $product->id,
                    'name'              => $product->name,
                    'product_code'      => $product->product_code,
                    'product_rak_name'  => optional(optional($product->product_rak)->rak)->name,
                    'product_cost'      => $product->product_cost,
                    'product_price'     => $product->product_price,
                    'tax'               => $product->tax,
                    'discount'          => $product->discount,
                    'retail_quantity'   => optional($stock)->available_quantity ?? 0,
                    'customer_price'    => $product->customer_price > 0 ? $product->customer_price : $product->product_price
                ];
            });
        return response()->json($product);
    }

    public function searchProductBySupplier(Request $request, $supplier_id)
    {
    
        $products = Product::where('supplier_id', $supplier_id)
            ->companies()
            ->with('barcode', 'product_rak.rak')
            ->select('id', 'product_code', 'product_name', 'product_cost', 'product_price', 'has_serial', 'tax')
            ->withCount(['warehouse_stocks as available_quantity' => function ($q) use ($request) {
                if ($request->warehouse_id) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }
                
                $q->select(DB::Raw('SUM(available_quantity)'));
            }])
            ->get()
            ->map(function ($product) {
                return [
                    'id'                => $product->id,
                    'product_name'      => $product->product_name,
                    'product_code'      => $product->product_code,
                    'product_price'     => $product->product_price,
                    'tax'               => $product->tax,
                    'has_serial'        => $product->has_serial,
                    'available_quantity'=> $product->available_quantity ?? 0,
                    'barcode'           => $product->barcode,
                    'product_rak_name'  => optional(optional($product->product_rak)->rak)->name,
                    'product_cost'      => $product->product_cost
                ];
            });

        return response()->json($products);
    }

    public function searchProductBySupplierCustomer(Request $request, $supplier_id)
    {
        $query = Product::companies()
            ->with('barcode', 'product_rak.rak')
            ->select('id', 'product_code', 'product_name', 'product_cost', 'product_price', 'has_serial', 'tax')
            ->withCount(['warehouse_stocks as available_quantity' => function ($q) use ($request) {
                if ($request->warehouse_id) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }
                $q->select(DB::Raw('SUM(available_quantity)'));
            }]);

        // 'all' means no supplier filter
        if ($supplier_id !== 'all') {
            $query->where('supplier_id', $supplier_id);
        }

        $products = $query->get()->map(function ($product) {
            return [
                'id'                => $product->id,
                'product_name'      => $product->product_name,
                'product_code'      => $product->product_code,
                'product_price'     => $product->product_price,
                'tax'               => $product->tax,
                'has_serial'        => $product->has_serial,
                'available_quantity'=> $product->available_quantity ?? 0,
                'barcode'           => $product->barcode,
                'product_rak_name'  => optional(optional($product->product_rak)->rak)->name,
                'product_cost'      => $product->product_cost,
            ];
        });

        $customer_advance = 0;
        $customer_due     = 0;

        if ($request->customer_id) {
            // Use the same live calculation as getCustomerSupplierBalance for consistency
            $service  = new \App\Services\Reports\CustomerReportServices();
            $customer = \App\Customer::where('id', $request->customer_id)->take(1)->get();

            if ($supplier_id === 'all') {
                $balances = $service->getCustomersBalances($customer, null, null);
            } else {
                $balances = $service->getCustomersBalances($customer, null, $supplier_id);
            }

            $balance = (float) ($balances->first()['balance'] ?? 0);

            if ($balance < 0) {
                $customer_advance = abs($balance);
                $customer_due     = 0;
            } else {
                $customer_advance = 0;
                $customer_due     = $balance;
            }
        }

        return response()->json([
            'products'         => $products,
            'customer_advance' => $customer_advance,
            'customer_due'     => $customer_due,
        ]);
    }



     public function customerBalance(Request $request){
        $customer = Customer::find($request->customer_id);
        $customer_balance = $customer ? (float) $customer->current_balance : 0;
        return response()->json($customer_balance);
    }

    public function getCustomerSupplierBalance(Request $request)
    {
        $customer_id = $request->customer_id;
        $supplier_id = $request->supplier_id ?: null;

        $service  = new CustomerReportServices();
        $customer = Customer::where('id', $customer_id)->take(1)->get();
        $balances = $service->getCustomersBalances($customer, null, $supplier_id);
        $balance  = (float) ($balances->first()['balance'] ?? 0);

        return response()->json([
            'previous_due'     => $balance > 0 ? $balance : 0,
            'advanced_payment' => $balance < 0 ? abs($balance) : 0,
        ]);
    }


    public function searchHolesaleProductByCustomerId(Request $request, $customer_id, $warehouse_id = null)
    {
        $product_like_search = Product::with('product_rak')->companies()
            ->with(['warehouse_stocks' => function ($q) use ($warehouse_id) {
                $q->where('warehouse_id', $warehouse_id);
            }])
            ->selectRaw('product_name as name, product_code, id, product_cost, product_price, holesale_price, tax, opening_quantity, discount')
            ->where('product_name', 'LIKE', "%" . $request->name . "%")
            ->orWhere('product_code', 'LIKE', "%" . $request->name . "%")
            ->orWhereHas('barcode', function ($q) use ($request) {
                $q->where('barcode_number', $request->name);
            })
            ->when($customer_id != null, function ($qr) use ($customer_id) {
                $qr->withCount(['pricing as customer_price' => function ($q) use ($customer_id) {
                    $q->where('customer_id', $customer_id)->select(DB::Raw('SUM(price)'));
                }]);
            })
            ->take(10)->get();

        $product = $product_like_search
            ->map(function ($product) {
                $stock = $product->warehouse_stocks->first();
                return [
                    'id'                => $product->id,
                    'name'              => $product->name,
                    'product_code'      => $product->product_code,
                    'product_rak_name'  => optional(optional($product->product_rak)->rak)->name,
                    'product_cost'      => $product->product_cost,
                    'product_price'     => $product->product_price,
                    'tax'               => $product->tax,
                    'discount'          => $product->discount,
                    'retail_quantity'   => optional($stock)->available_quantity ?? 0,
                    'customer_price'    => $product->customer_price > 0 ? $product->customer_price : ($product->holesale_price > 0 ? $product->holesale_price : $product->product_price)
                ];
            });
        return response()->json($product);
    }


    public function checkCustomerPrice(Request $request)
    {
        $customerId = $request->customerId;
        $productId = $request->productId;


        $customerProduct = CustomerPricing::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();

        if ($customerProduct) {

            $price = $customerProduct->price;
            return response()->json(['price' => $price]);
        } else {

            return response()->json(['price' => null]);
        }
    }


    public function getCustomer(Request $request)
    {
        $term = $request->term;
        $termNumber = preg_replace('/[^0-9]/', '', $term);
        return $customers = Customer::companies()
            ->where(function ($q) use ($term, $termNumber) {
                $q->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('id', '=', $term)
                    ->orWhere('customer_code', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%")
                    ->orWhere('phone', 'LIKE', "%{$term}%");
                if (!empty($termNumber)) {
                    $q->orWhere('id', '=', (int)$termNumber);
                }
            })
            ->take(10)
            ->get()

            ->map(function ($customer) {
                $balance = (float) $customer->current_balance;
                
                return [
                    'value' => $customer->customerId . ' - ' . $customer->name,
                    'id' => $customer->id,
                    'phone' => $customer->phone,
                    'customer_code' => $customer->customer_code,
                    'advanced_payment' => $balance < 0 ? abs($balance) : 0,
                    'previous_due' => $balance > 0 ? $balance : 0,
                ];
            })->toArray();

            // dd($customers);

        return response($customers, 200);
    }

    public function getCustomerSupplierwise(Request $request)
    {
        $term        = $request->term;
        $supplier_id = $request->supplier_id; // optional

        $termNumber = preg_replace('/[^0-9]/', '', $term);
        $customers = Customer::companies()
            ->where(function ($q) use ($term, $termNumber) {
                $q->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('id', '=', $term)
                    ->orWhere('customer_code', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%")
                    ->orWhere('phone', 'LIKE', "%{$term}%");
                if (!empty($termNumber)) {
                    $q->orWhere('id', '=', (int)$termNumber);
                }
            })
            ->take(10)
            ->get()
            ->map(function ($customer) use ($supplier_id) {
                $service  = new CustomerReportServices();
                $balances = $service->getCustomersBalances(collect([$customer]), null, $supplier_id ?: null);
                $balance  = (float) ($balances->first()['balance'] ?? 0);

                return [
                    'value'            => $customer->customerId . ' - ' . $customer->name,
                    'id'               => $customer->id,
                    'phone'            => $customer->phone,
                    'customer_code'    => $customer->customer_code,
                    'previous_due'     => $balance > 0 ? $balance : 0,
                    'advanced_payment' => $balance < 0 ? abs($balance) : 0,
                ];
            })
            ->values()
            ->toArray();

        return response()->json($customers);
    }

    public function getCustomerBalance($customer_id)
    {

        $customer = Customer::with('customer_category')->find($customer_id);

        $balance = (float) $customer->current_balance;

        $data['previous_due']       = $balance >= 0 ? $balance : 0;
        $data['advanced_payment']   = $balance < 0 ? abs($balance) : 0;
        $data['due_limit']          = $customer->due_limit ?? 999999999;
        $data['amount_of']          = optional($customer->customer_category)->amount_of ?? 0;
        $data['amount']             = optional($customer->customer_category)->amount ?? 0;
        $data['type']               = optional($customer->customer_category)->type ?? 'nothing';
        $data['category']           = $customer->customer_category;

        return  response()->json($data);
    }

    public function get_due_collection()
    {
        return view('admin.sales.due-collection');
    }



    public function getSaleReturns($customer_id)
    {
        return $saleReturnAmount = Transaction::companies()->whereHasMorph('transactionable', RandomReturn::class, function ($q) use ($customer_id) {
            $q->where('fk_customer_id', $customer_id);
        })
            ->with('transactionable:amount,id')
            ->get()
            ->map(function ($item) {
                $data['return_amount'] = abs($item->transactionable->amount);
                return $data;
            })->sum('return_amount');
    }


    private function getCustomerTransactionsAmount($customer_id)
    {
        return $transactions = Transaction::companies()->whereHasMorph('transactionable', AccountReview::class, function ($q) use ($customer_id) {
            $q->where('transactionable_type', Customer::class)
                ->where('transactionable_id', $customer_id);
        })->sum('amount');
    }


    private function getCustomerSaleAmount($customer_id)
    {
        return $purchases = Sale::companies()->where('fk_customer_id', $customer_id)
            ->withCount(['sale_details AS total_amount' => function ($query) {
                $query->select(\DB::raw("SUM(product_sub_total) as total"));
            }])->get()->sum(function ($item) {
                return ($item->total_amount + $item->invoice_tax + $item->currier_amount - $item->invoice_discount - $item->paid);
            });
    }

    public function sendMessage(Request $request, Sale $sale)
    {
        if (optional($sale->customer)->phone && $request->type == 'sms') {
            $response = $this->sendSms($sale, $request);
            if ($response == 'Success') {
                return back()->withSuccess('Message Successfully send');
            }
        } else if (optional($sale->customer)->email && $request->type == 'email') {

            $data       = Sale::with(['sale_details', 'sale_customer'])->withCount(['sale_details as total_sale_amount' => function ($q) {
                $q->select(DB::Raw('SUM(product_sub_total)'));
            }])->findOrFail($sale->id);

            $view_file = "mail.customer-invoice";
            $subject = $sale->invoiceId . " sales invoice";


            try {
                Mail::to(optional($sale->customer)->email)->send(new MailSender($data, $view_file, $subject));
                return back()->withSuccess('Mail Successfully Send');
            } catch (\Exception $ex) {
                return $ex->getMessage();
            }
        }
        return back()->withErrors('Message Can Not Be Send');
    }

    private function sendSms($sale, $request)
    {
        $current_bill = round((float) $request->sub_total);
        $previous_due = round((float) $request->previous_due);
        $total_payable = round((float) $request->total_payable);
        $paid = round((float) $request->receive_amount);
        $due = round((float) $sale->due);

        $date = $sale->sale_date ? date('d/m/Y', strtotime($sale->sale_date)) : date('d/m/Y');
        $customer_name = optional($sale->customer)->name ?? 'গ্রাহক';
        $company_name = optional($sale->sale_company)->name ?? 'কোম্পানি';

        $text = "প্রিয় গ্রাহক, \n  {$date} তারিখে {$customer_name}-এর নামে ৳{$current_bill} টাকার একটি ইনভয়েস করা হয়েছে।\n";
        
        if ($sale->supplier_id && $sale->supplier) {
            $text .= "সাপ্লায়ার: {$sale->supplier->name}\n";
        }

        if ($previous_due >= 1) {
            $text .= "পূর্বের বকেয়া: ৳{$previous_due}\n";
        }
        $text .= "বর্তমান ইনভয়েস: ৳{$current_bill}\n";
        
        if ($paid > 0) {
            $text .= "জমা: ৳{$paid}\n";
        }
        
        $text .= "মোট বকেয়া: ৳{$due}\n";
        $text .= "ধন্যবাদান্তে,\n {$company_name}";

        $request['numbers'] = getValidMobileNumber($sale->customer->phone);
        $request['text']    = $text;

        return $response = (new SmsSendController())->sendSmsViaHttpGuzzle($request);
    }
}
