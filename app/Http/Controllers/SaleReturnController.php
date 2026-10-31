<?php

namespace App\Http\Controllers;


use App\ProductSerialSalesDetails;
use App\Services\SalesService;
use Illuminate\Http\Request;
use App\Models\RandomReturn;
use App\ProductStock;
use App\SaleReturn;
use App\Customer;
use App\Product;

use App\Sale;

use App\Account;


use Carbon\Carbon;


use App\SalesDetails;
use App\ProductSerial;
use App\SaleReturnDetail;



use Illuminate\Support\Facades\DB;
use App\Models\Inventory\ProductDamage;
use App\Models\Inventory\ProductDamageItem;

class SaleReturnController extends Controller
{

    public function index()
    {
        $data['randomReturns'] = RandomReturn::companies()->userLog()->orderByDesc('id')->get();
        $data['saleReturns'] = SaleReturn::companies()->userLog()->orderByDesc('id')->paginate(30);

        return view('admin.return.sales.index', $data);
    }

    public function exchanges()
    {
        $data['saleExchanges'] = SaleReturn::companies()->userLog()->where('exchange_amount', '>', 0)->orderByDesc('id')->paginate(30);

        return view('admin.return.sales.exchange_index', $data);
    }

    public function getSupplierCustomerDue(Request $request)
    {
        $due = 0;
        if ($request->customer_id && $request->supplier_id) {
            $record = \App\Models\CustomerSupplier::where('customer_id', $request->customer_id)
                ->where('supplier_id', $request->supplier_id)
                ->first();
            if ($record) {
                $due = $record->balance;
            }
        }
        return response()->json(['due' => $due]);
    }

    public function getSupplierProducts(Request $request)
    {
        $supplier_id = $request->supplier_id;
        $products = Product::where('supplier_id', $supplier_id)
            ->companies()
            ->with('product_stock')
            ->get();
            
        return response()->json($products);
    }

    public function create()
    {
        $data['customers']  = Customer::companies()->select('id', 'fk_company_id', 'name', 'phone', 'customer_code', 'default', 'current_balance as balance', 'due_limit')->get();
        $data['accounts']   = Account::getAccountWithBalance();
        $data['products']   = Product::companies()
                                ->with(['product_stock' => function($q) {
                                    $q->whereNull('warehouse_id');
                                }])
                                ->select('id', 'product_name', 'product_code', 'product_price')
                                ->get();
        
        $data['supplierWiseReturnEnabled'] = \App\SoftwareSetting::companies()->where('title', 'Supplier Wise Return Without Invoice')->where('options', 'yes')->exists();
        if ($data['supplierWiseReturnEnabled']) {
            $data['suppliers'] = \App\Supplier::companies()->get();
        }

        return view('admin.return.sales.create', $data);
    }



    public function store(Request $request)
    {
        // dd($request->all());
        try {
            $target_supplier_id = null;
            if ($request->sale_id) {
                $original_sale = \App\Sale::find($request->sale_id);
                if ($original_sale && $original_sale->supplier_id) {
                    $target_supplier_id = $original_sale->supplier_id;
                }
            } elseif ($request->supplier_id) {
                $target_supplier_id = $request->supplier_id;
            }

            DB::transaction(function () use($request, $target_supplier_id) {

                $invoice = $this->makeInvoice($request, $target_supplier_id);

                $this->storeSaleReturnDetails($invoice, $request);

                $this->storeSaleExchangeDetails($invoice, $request);

                $this->makeTransaction($invoice, $request);
            });

            // update customer balance
            (new SalesService())->updateCustomerBalance($request->customer_id);

            // update supplier-specific customer balance
            if ($target_supplier_id) {
                $net_due_reduction = ($request->total_amount - ($request->ex_total_amount ?? 0)) - ($request->return_amount ?? 0);
                if ($net_due_reduction != 0) {
                    (new \App\Services\SalesService())->updateCustomerSupplierBalanceByDelta($request->customer_id, $target_supplier_id, -1 * $net_due_reduction);
                }
            }

            $this->sendReturnSms($request);

            return back()->withSuccess('Product Returned Successfully');
        } catch (\Throwable $th) {
            return back()->withError($th->getMessage());
        }
    }


    private function makeInvoice($request, $target_supplier_id = null)
    {
        // dd($request->all());
        $saleReturn = SaleReturn::create([
            'date'              => fdate(today(), 'Y-m-d'),
            'customer_id'       => $request->customer_id,
            'supplier_id'       => $target_supplier_id,
            'sale_id'           => $request->sale_id,
            'amount'            => $request->total_amount,
            'exchange_amount'   => $request->ex_total_amount ?? 0,
            'reference'         => $request->reference,
            'comment'           => $request->comment,
            'paid_amount'       => $request->return_amount ?? 0,
            'received_amount'   => $request->received_amount ?? 0,
            'previous_due'      => $request->previous_due,
            'available_amount'  => $request->current_balance,
        ]);

        $saleReturn->update([
            'invoice_no' => '#SR-' . (1000000 + $saleReturn->id)
        ]);
        return $saleReturn;
    }

    private function storeSaleExchangeDetails($invoice, $request)
    {
        if (isset($request->ex_product_ids)) {
            foreach ($request->ex_product_ids as $key => $product_id) {
                $item = $invoice->sale_return_exchanges()->create([
                    'product_id'        =>  $product_id,
                    'quantity'          =>  $request->ex_quantities[$key],
                    'price'             =>  $request->ex_prices[$key],
                ]);

                $this->stockManageExchange($item);
            }
        }
    }



    private function storeSaleReturnDetails($invoice, $request)
    {
        // dd($request->all());
        foreach ($request->product_ids as $key => $product_id) {
            $item = $invoice->sale_return_details()->create([
                'product_id'        =>  $product_id,
                'quantity'          =>  $request->quantities[$key],
                'price'             =>  $request->prices[$key],
                'condition_type'    =>  $request->condition_type[$key],
            ]);
            if($request->condition_type[$key] == 'Good'){
                 $this->stockManage($item);

                 if(isset($request->product_serials[$request->product_ids[$key]]))
                 {
                    $this->updateProductSerial($request->product_serials[$request->product_ids[$key]]);
                 }

            }
            else{
                $damageId = $this->makeWastage($request, $key);
                $item->update(['product_damage_id' => $damageId]);
            }
        }
    }


    private function updateProductSerial($serials)
    {
        foreach($serials as $serial)
        {
            ProductSerial::where('serial', $serial)->first()->update([
                'is_sold' => false,
                'sale_id' => null
            ]);
        }
    }


    private function stockManage($item)
    {
        $stock = ProductStock::where('fk_product_id', $item->product_id)->where('fk_company_id', companyId())->whereNull('warehouse_id')->first();

        if (!$stock) {
            $stock = ProductStock::create([
                'fk_product_id'         => $item->product_id,
                'sold_quantity'         => -$item->quantity,
                'available_quantity'    => $item->quantity
            ]);
        } else {
            $stock->increment('available_quantity', $item->quantity);
            $stock->decrement('sold_quantity', $item->quantity);
        }
    }

    private function stockManageExchange($item)
    {
        $stock = ProductStock::where('fk_product_id', $item->product_id)->where('fk_company_id', companyId())->whereNull('warehouse_id')->first();

        if (!$stock) {
            $stock = ProductStock::create([
                'fk_product_id'         => $item->product_id,
                'sold_quantity'         => $item->quantity,
                'available_quantity'    => -$item->quantity
            ]);
        } else {
            $stock->decrement('available_quantity', $item->quantity);
            $stock->increment('sold_quantity', $item->quantity);
        }
    }

    private function makeWastage($request, $key)
    {
        $product_damage = ProductDamage::create([
            'date'      => Carbon::now()->format('y-m-d'),
            'amount'    => $request->prices[$key],
        ]);

        $damagedItem = ProductDamageItem::create([
            'product_damage_id' => $product_damage->id,
            'fk_product_id'     => $request->product_ids[$key],
            'description'       => $request->comment,
            'type'              => 'damaged',
            'quantity'          => $request->quantities[$key],
            'price'             => $request->prices[$key],
        ]);

        $damagedItem->wastage()->create([
            'wastagable_type'   => ProductDamage::class,
            'wastagable_id'     => $product_damage->id,
            'quantity'          => $request->quantities[$key],
        ]);


        // Stock থেকে wastage বাদ দাও
        $stock = ProductStock::where('fk_product_id', $request->product_ids[$key])
            ->where('fk_company_id', companyId())
            ->whereNull('warehouse_id')
            ->first();

        if ($stock) {
            $stock->increment('wastage_quantity', $request->quantities[$key]);
            $stock->decrement('sold_quantity', $request->quantities[$key]);
        }
        
        return $product_damage->id;
    }

    private function makeTransaction($invoice, $request)
    {
        $account = defaultAccount();
        if (!$account) {
            throw new \Exception('No default account found! Please set a default account.');
        }

        if ($invoice->paid_amount > 0) {
            $transaction = $invoice->transaction()->create([
                'fk_account_id' => $account->id,
                'date'          => $invoice->date,
                'amount'        => -$invoice->paid_amount // Outflow because we return money to customer
            ]);
        }

        if ($invoice->received_amount > 0) {
            $customer = \App\Customer::find($invoice->customer_id);
            if ($customer) {
                // Determine supplier_id
                $target_supplier_id = null;
                if ($request->sale_id) {
                    $original_sale = \App\Sale::find($request->sale_id);
                    if ($original_sale && $original_sale->supplier_id) {
                        $target_supplier_id = $original_sale->supplier_id;
                    }
                } elseif ($request->supplier_id) {
                    $target_supplier_id = $request->supplier_id;
                }

                $review = $customer->accountReviews()->create([
                    'type' => 'advance',
                    'amount' => $invoice->received_amount,
                    'sale_return_id' => $invoice->id,
                    'fk_supplier_id' => $target_supplier_id,
                ]);

                $review->makeOpeningDue($invoice->received_amount);
            }
        }
    }



    public function show(SaleReturn $saleReturn)
    {
        return view('admin.return.sales.show', compact('saleReturn'));
    }




    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $saleReturn = SaleReturn::find($id);
                $customer_id = $saleReturn->customer_id;

                $sale_id = $saleReturn->sale_id;
                $return_delta = $saleReturn->amount - $saleReturn->exchange_amount;

                $saleReturn->transaction()->delete();

                $accountReview = \App\AccountReview::where('sale_return_id', $id)->first();
                if ($accountReview) {
                    $accountReview->transaction()->delete();
                    $accountReview->delete();
                }

                foreach ($saleReturn->sale_return_details as $key => $detail) {
                    if ($detail->condition_type === 'Damage') {
                        $stock = ProductStock::companies()->where('fk_product_id', $detail->product_id)->whereNull('warehouse_id')->first();
                        if ($stock) {
                            $stock->decrement('wastage_quantity', $detail->quantity);
                            $stock->increment('available_quantity', $detail->quantity);
                        }
                        if ($detail->product_damage_id) {
                            $damage = \App\Models\Inventory\ProductDamage::find($detail->product_damage_id);
                            if ($damage) {
                                foreach ($damage->items as $dItem) {
                                    $dItem->wastage()->delete();
                                    $dItem->delete();
                                }
                                $damage->delete();
                            }
                        }
                    } else {
                        $this->updateStock($detail);
                    }
                    $detail->delete();
                }
                foreach ($saleReturn->sale_return_exchanges as $exItem) {
                    $stock = ProductStock::companies()->where('fk_product_id', $exItem->product_id)->whereNull('warehouse_id')->first();
                    if ($stock) {
                        $stock->increment('available_quantity', $exItem->quantity);
                        $stock->decrement('sold_quantity', $exItem->quantity);
                    }
                    $exItem->delete();
                }

                $saleReturn->delete();

                (new SalesService())->updateCustomerBalance($customer_id);
                $target_supplier_id = $saleReturn->supplier_id;
                if (!$target_supplier_id && $sale_id) {
                    // Fallback for old returns that didn't have supplier_id saved
                    $original_sale = Sale::find($sale_id);
                    if ($original_sale && $original_sale->supplier_id) {
                        $target_supplier_id = $original_sale->supplier_id;
                    }
                }
                
                if ($target_supplier_id) {
                    (new SalesService())->updateCustomerSupplierBalanceByDelta($customer_id, $target_supplier_id, $return_delta);
                }
            });
            return back()->withSuccess('Sale Return Deleted Successfully');
        } catch (\Exception $ex) {
            return redirect()->back()->withError($ex->getMessage());
        }
    }



    private function updateStock($item)
    {
        $stock = ProductStock::companies()->where('fk_product_id', $item->product_id)->whereNull('warehouse_id')->first();

        if ($stock) {
            $stock->decrement('available_quantity', $item->quantity);
            $stock->increment('sold_quantity', $item->quantity);
            $stock->save();
        }
    }

    public function getCustomerInvoices(Request $request)
    {
        return Sale::companies()->where('fk_customer_id', $request->customer_id)->get()->map(function($sale) {
            $data['id'] = $sale->id;
            $data['invoice_no'] = $sale->invoiceId;
            return $data;
        });
    }

    public function getInvoiceId(Request $request)
    {
        $serial = ProductSerial::where('serial', $request->serial_id)->first();
        if (!$serial) return response()->json(null);

        $salesDetail = SalesDetails::where('fk_product_id', $serial->product_id)->first();
        if (!$salesDetail) return response()->json(null);

        $sale = Sale::where('id', $salesDetail->fk_sales_id)->first();
        if (!$sale) return response()->json(null);

        $data['saleId']      = $sale->id;
        $data['customer_id'] = $sale->fk_customer_id;
        $data['invoice_no']  = $sale->invoiceId;
        return $data;
    }

    public function getInvoiceIdBySerial(Request $request)
    {
        $sale   = ProductSerial::whereHas('sales', function ($query) use ($request) {
            return $query->where('serial', $request->serial_id);
        })->first();

        $sale = Sale::find($sale->sale_id);

        $data['saleId']      = $sale->id;
        $data['customer_id'] = $sale->fk_customer_id;
        $data['invoice_no']  = $sale->invoiceId;

        return $data;
    }


    public function getCustomerData(Request $request)
    {
        $Customer = Customer::companies()->where('id', $request->customer_id)->first();
        return $Customer;
    }




    public function getCustomerBuyingProducts(Request $request)
    {
        if($request->invoice_id != '') {

            $sale = Sale::find($request->invoice_id);

            return Product::withCount(['sales_details as quantity' => function($q) use($request) {
                                return $q->where('fk_sales_id', $request->invoice_id)->select(DB::Raw('SUM(quantity)'));
                            }])->whereHas('sales_details', function($q) use($request) {
                                $q->where('fk_sales_id', $request->invoice_id);
                            })
                            ->get()->map(function($item) use($sale) {
                                $detail = SalesDetails::where('fk_sales_id', $sale->id)
                                    ->where('fk_product_id', $item->id)
                                    ->first();

                                $original_price = $detail ? $detail->unit_price : $item->product_price;
                                $product_discount = $detail ? ($detail->product_discount ?? 0) : 0;
                                
                                // Base price after product discount
                                $base_price = $original_price - $product_discount;
                                
                                // Calculate invoice-level discount share
                                $invoice_discounts = ($sale->invoice_discount ?? 0) + ($sale->special_discount ?? 0);
                                $invoice_discount_share = 0;
                                
                                if ($invoice_discounts > 0) {
                                    $total_invoice_product_subtotal = SalesDetails::where('fk_sales_id', $sale->id)
                                        ->get()
                                        ->sum(function($d) {
                                            return ($d->unit_price - ($d->product_discount ?? 0)) * $d->quantity;
                                        });
                                        
                                    if ($total_invoice_product_subtotal > 0) {
                                        $discount_ratio = $invoice_discounts / $total_invoice_product_subtotal;
                                        $invoice_discount_share = $base_price * $discount_ratio;
                                    }
                                }
                                
                                $net_price_paid = $base_price - $invoice_discount_share;
                                if ($net_price_paid < 0) {
                                    $net_price_paid = 0;
                                }

                                $data['id']                     = $item->id;
                                $data['name']                   = $item->product_name;
                                $data['code']                   = $item->product_code;
                                $data['original_price']         = round($original_price, 2);
                                $data['product_discount']       = round($product_discount, 2);
                                $data['invoice_discount_share'] = round($invoice_discount_share, 2);
                                $data['price']                  = round($net_price_paid, 2);
                                $data['quantity']               = $this->customerReturnableQty($item->id, $item->quantity, $sale->id);
                                $data['warranty_left']          = '';
                                $data['guarantee_left']         = '';
                                $data['sale_id']                = $sale->id;

                                if ($item->warranty_days > 0) {
                                    $left_days = Carbon::parse($sale->sale_date)->addDays((int)$item->warranty_days)->diffInDays(Carbon::parse(now()));
                                    if($left_days >= 0) {
                                       $data['warranty_left'] = 'Warranty left ' . $left_days . ' days';
                                    } else {
                                        $data['warranty_left'] = 'Warranty Period Expired';
                                    }
                                }
                                if ($item->guarantee_days > 0) {
                                    $left_days = Carbon::parse($sale->sale_date)->addDays((int)$item->guarantee_days)->diffInDays(Carbon::parse(now()));
                                    if($left_days >= 0) {
                                       $data['guarantee_left'] = 'Guarantee left ' . $left_days . ' days';
                                    } else {
                                        $data['guarantee_left'] = 'Guarantee Period Expired';
                                    }
                                }
                                return $data;
                            });

        } else {
            return Product::withCount(['sales_details as quantity' => function($q) use($request) {
                    $q->whereHas('sale', function($qr) use($request) {
                        $qr->where('fk_customer_id', $request->customer_id);
                    })->select(DB::Raw('SUM(quantity)'));
            }])
            ->with(['product_stock'=>function($query){
                return $query->select('available_quantity','fk_product_id');
            }])
            ->get()->map(function($item) use($request) {
                $data['id']                     = $item->id;
                $data['name']                   = $item->product_name;
                $data['code']                   = $item->product_code;
                $data['original_price']         = $item->product_price;
                $data['product_discount']       = 0;
                $data['invoice_discount_share'] = 0;
                $data['price']                  = $item->product_price;
                $data['quantity']               = $this->customerReturnableQty($item->id, $item->quantity);
                $data['product_stock']          = $item->product_stock->available_quantity ?? 0;
                $data['warranty_left']          = '';
                $data['guarantee_left']         = '';
                return $data;
            });
        }
    }


    private function customerReturnableQty($product_id, $qty, $sale_id = null)
    {
        $returned = SaleReturnDetail::where('product_id', $product_id)
        -> whereHas('sale_return', function($q) use($sale_id){
            if ($sale_id) {
                return $q->where('sale_id', $sale_id);
            }
            return $q;
        })
            ->get()->sum(function($item){
                return $item->quantity;
            });
        return $qty - $returned;
    }


    public function getCustomerSerialProducts(Request $request)
    {
        if($request->sale_id != '') {
            return $sale = ProductSerial::where('sale_id', $request->sale_id)->get();
        }

    }

    private function sendReturnSms($request)
    {
        if ($request->filled('is_send_message')) {
            $customer = \App\Customer::find($request->customer_id);
            if (!$customer) return;

            $company_name = optional(auth()->user()->company)->name ?? 'কোম্পানি';
            $date = $request->date ? date('d/m/Y', strtotime($request->date)) : date('d/m/Y');
            
            $previous_due = round((float) $request->previous_due);
            $return_amount = round((float) $request->total_amount);
            $exchange_amount = round((float) $request->ex_total_amount);
            $paid_amount = round((float) $request->return_amount);
            $received_amount = round((float) $request->received_amount);
            
            $customer->refresh();
            $current_due = round((float) $customer->current_balance);

            $text = "প্রিয় গ্রাহক,\n {$date} তারিখে {$customer->name}-এর নামে একটি প্রোডাক্ট রিটার্ন করা হয়েছে।\n";
            if ($previous_due >= 1) {
                $text .= "পূর্বের বকেয়া: ৳{$previous_due}\n";
            }
            if ($return_amount > 0) {
                $text .= "রিটার্ন প্রোডাক্ট: ৳{$return_amount}\n";
            }
            if ($exchange_amount > 0) {
                $text .= "এক্সচেঞ্জ প্রোডাক্ট: ৳{$exchange_amount}\n";
            }
            if ($paid_amount > 0) {
                $text .= "ক্যাশ রিফান্ড: ৳{$paid_amount}\n";
            }
            if ($received_amount > 0) {
                $text .= "নগদ গ্রহণ: ৳{$received_amount}\n";
            }
            $text .= "বর্তমান বকেয়া: ৳{$current_due}\n";
            $text .= "ধন্যবাদান্তে,\n {$company_name}";

            $request['numbers'] = getValidMobileNumber($customer->phone);
            $request['text']    = $text;

            (new \App\Http\Controllers\SmsSendController())->sendSmsViaHttpGuzzle($request);
        }
    }
}