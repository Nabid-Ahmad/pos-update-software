<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Services\PurchaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

use App\{ AccountReview, PurchaseReturn, Purchase, Warehouse, Supplier, Product, ProductStock, Status, Transaction, Account, ProductSerial};

class PurchaseController extends Controller
{
    // use CurrentBalance;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Purchase::withCount(['purchase_details as purchase_qty'=>function($q){
            $q->select(DB::raw("SUM(quantity)"));
        }])->companies()->userLog()->latest()->totalAmount();

        if ($request->filled('supplier_id')) {
            $query->where('fk_supplier_id', $request->supplier_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('purchase_date', 'like', "%{$search}%")
                  ->orWhere('invoice_id', 'like', "%{$search}%")
                  ->orWhere('chalan_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $data['purchases'] = $query->paginate(30);
        $data['suppliers'] = \App\Supplier::companies()->pluck('name', 'id');
        
        return view('admin.purchase.purchases.index', $data);
    }

    public function create()
    {
        $data['account_infos']      = Account::getAccountWithBalance();
        $data['pendingStatusId']    = Status::where('name', 'Pending')->first()->id;
        $data['suppliers']          = Supplier::companies()->get();
        $data['products']           = Product::companies()->get();
        $data['warehouses']         = Warehouse::companies()->pluck('name', 'id');
        $data['currentBalance']     = 0;

        return view('admin.purchase.purchases.supplier-wise-purchase-create', $data);
    }

    public function individualPurchase()
    {
        $data['account_infos']      = Account::getAccountWithBalance();
        $data['pendingStatusId']    = Status::where('name', 'Pending')->first()->id;
        $data['suppliers']          = Supplier::companies()->pluck('name', 'id');
        $data['warehouses']         = Warehouse::companies()->pluck('name', 'id');
        $data['products']           = Product::companies()->with('barcode')->select('id', 'product_code', 'product_name', 'product_cost', 'has_serial')->get();
        $data['currentBalance']     = 0;

        return view('admin.purchase.purchases.individual-purchase', $data);
    }



    public function store(PurchaseRequest $request)
    {


        try {
            $purchase = $request->storePurchase();
        } catch (\Exception $exception) {
            return $exception->getMessage();

//                return redirect()->back()->with('errors', 'An error occurred: ' . $exception->getMessage());

        }

        return redirect()->route('purchases.show', $purchase->id)->withSuccess("Purchase Created Successfully!");
    }

    public function approve(PurchaseRequest $request, Purchase $purchase)
    {
        $request->approve($purchase);
        (new PurchaseService())->updateSupplierBalance($purchase->fk_supplier_id);
        return back()->withSuccess('Purchase Approved Successfully!');
    }



    public function show($id)
    {
        $purchase =  Purchase::with('purchase_supplier')->find($id);
        return view('admin.purchase.purchases.show', compact('purchase'));
    }



    public function destroy(Purchase $purchase)
    {
        $supplier_id = $purchase->fk_supplier_id;

        $status = DB::transaction(function ()use($purchase) {
            $errCount = 0;


            if ($purchase->isApproved()) {
                $purchase_id = $purchase->id;
                ProductSerial::where('purchase_id', $purchase_id)->delete();

                foreach ($purchase->purchase_details as $item) {

                    // Serials are already deleted by purchase_id above
                    $productStock = ProductStock::companies()->where('fk_product_id', $item->fk_product_id)->where('warehouse_id', $item->warehouse_id)->first();

                    if ($productStock) {
                        $freeQty    = floatval($item->free_quantity ?? 0);
                        $totalQty   = $item->quantity + $freeQty;
                        $hasFreeQty = Schema::hasColumn('product_stocks', 'free_quantity');

                        $updateData = [
                            'purchased_quantity' => $productStock->purchased_quantity - $item->quantity,
                            'available_quantity' => $productStock->available_quantity - $totalQty,
                        ];
                        if ($hasFreeQty) {
                            $updateData['free_quantity'] = max(0, floatval($productStock->free_quantity ?? 0) - $freeQty);
                        }
                        $productStock->update($updateData);
                    }
                }
            }

            $purchase->delete();
            (new PurchaseService())->updateSupplierBalance($purchase->fk_supplier_id);

            return ['success' => 'Purchase Deleted Successfully!'];
        });

        return redirect('purchases')->with($status);
    }





    public function get_supplier_product($id)
    {
        $supplier_products = Product::companies()->where('supplier_id', $id)->get();

        if ($supplier_products->isNotEmpty()) {
            return view('admin.purchases.products', [
                'supplier_products' => $supplier_products
            ]);
        }
        return [];
    }

    public function getSupplierBalance ($supplier_id)
    {
        $supplier = Supplier::find($supplier_id);

        $data['previous_due']       = $supplier->current_balance > 0 ? $supplier->current_balance : 0;
        $data['advanced_payment']   = $supplier->current_balance < 0 ? -$supplier->current_balance : 0;

        return response()->json($data);
    }


    public function getSupplierTransactions($supplier_id)
    {
        $transactions = Transaction::companies()->whereHasMorph('transactionable', AccountReview::class, function ($q) use ($supplier_id) {
            $q->where('transactionable_type', Supplier::class)
                ->where('transactionable_id', $supplier_id);
        })->sum('amount');

        return abs($transactions);
    }

    public function getPurchaseReturns($supplier_id)
    {
        $purchaseReturns = Transaction::companies()->whereHasMorph('transactionable', PurchaseReturn::class, function ($q) use ($supplier_id) {
            $q->where('supplier_id', $supplier_id);
        })->sum('amount');

        return abs($purchaseReturns);
    }


    public function getPurchases($supplier_id)
    {
        return $purchases = Purchase::companies()->where('fk_supplier_id', $supplier_id)
            ->withCount(['purchase_details AS total_amount' => function ($query) {
                $query->select(\DB::raw("SUM(product_sub_total) as total"));
            }])->get()->sum(function ($item) {
                return $item->total_amount - ($item->invoice_discount + $item->paid_amount);
            });
    }
}
