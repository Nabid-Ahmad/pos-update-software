<?php

namespace App\Http\Controllers\Reports;

use App\Brand;
use App\Product;
use App\Category;
use App\Warehouse;
use App\SalesDetails;
use App\SoftwareSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class InventoryReportController extends Controller
{

    public function topsellproduct(Request $request)
    {
        $data['count'] = 0;
        $data['selectproducts'] = Product::companies()->get();
        $data['customers'] = \App\Customer::companies()->get(['id', 'name', 'phone']);
        $data['suppliers'] = \App\Supplier::companies()->get(['id', 'name']);


        $query = SalesDetails::with(['product.supplier'])
            ->whereHas('sale', function($q) {
                $q->companies();
            })
            ->select('sales_details.fk_product_id', DB::raw('SUM(sales_details.quantity) as total_quantity'))
            ->when($request->filled('start_date') || $request->filled('end_date'), function ($p) use ($request) {
                $p->whereHas('sale', function($q) use($request) {
                    $q->whereBetween('sale_date', [$request->start_date, $request->end_date]);
                });
            })
            ->when($request->filled('fk_customer_id'), function ($p) use ($request) {
                $p->whereHas('sale', function($q) use($request) {
                    $q->where('fk_customer_id', $request->fk_customer_id);
                });
            })
            ->when($request->filled('fk_supplier_id'), function ($q) use ($request) {
                $q->whereHas('product', function($p) use ($request) {
                    $p->where('supplier_id', $request->fk_supplier_id);
                });
            })
            ->when($request->filled('fk_product_id'), function ($q) use ($request) {
                $q->where('sales_details.fk_product_id', $request->fk_product_id);
            });

        if ($request->filled('fk_product_id')) {
            $query->join('sales', 'sales.id', '=', 'sales_details.fk_sales_id')
                  ->join('customers', 'customers.id', '=', 'sales.fk_customer_id')
                  ->addSelect('customers.name as customer_name')
                  ->groupBy('sales_details.fk_product_id', 'sales.fk_customer_id', 'customers.name');
        } else {
            $query->groupBy('sales_details.fk_product_id');
        }

        $data['topSellProducts'] = $query->orderByDesc('total_quantity')->get();

        
        return view('admin.reports.inventory-reports.topSellProduct', $data);
    }

    public function productStockReport(Request $request)
    {
        $data['selectproducts'] = Product::companies()->pluck('product_name', 'id');
        $data['categories']     = Category::companies()->with('subcategories')->get();
        $data['brands']         = Brand::companies()->pluck('name', 'id');
        $data['warehouses']     = Warehouse::companies()->pluck('name', 'id');
        $data['settings']       = SoftwareSetting::companies()->get();

        $hasFreeQuantity = \Illuminate\Support\Facades\Schema::hasColumn('product_stocks', 'free_quantity');

        $products = Product::companies()
            ->when($request->filled('brand_id'), function ($q) use($request) {
                $q->where('brand_id', $request->brand_id);
            })
            ->when($request->filled('category_id'), function ($q) use($request) {
                $q->where('fk_category_id', $request->category_id);
            })
            ->when($request->filled('fk_sub_category_id'), function ($q) use($request) {
                $q->where('fk_sub_category_id', $request->fk_sub_category_id);
            })
            ->when($request->filled('product_id'), function ($q) use($request) {
                $q->where('id', $request->product_id);
            })
            ->when($request->warehouse_id == 'all', function ($q) use ($request, $hasFreeQuantity) {
                $q->withSum('warehouse_stocks as opening_quantity', 'opening_quantity')
                    ->withSum('warehouse_stocks as purchased_quantity', 'purchased_quantity');
                
                if ($hasFreeQuantity) {
                    $q->withSum('warehouse_stocks as free_quantity', 'free_quantity');
                }

                $q->withSum('warehouse_stocks as sold_quantity', 'sold_quantity')
                    ->withSum('warehouse_stocks as wastage_quantity', 'wastage_quantity')
                    ->withSum('warehouse_stocks as transfer_out', 'transfer_out')
                    ->withSum('warehouse_stocks as transfer_in', 'transfer_in')
                    ->withSum('warehouse_stocks as available_quantity', 'available_quantity');

            })->when($request->warehouse_id != 'all', function ($qr) use ($request, $hasFreeQuantity) {

                $qr->withSum(['warehouse_stocks as opening_quantity' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'opening_quantity')
                ->withSum(['warehouse_stocks as purchased_quantity' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'purchased_quantity');

                if ($hasFreeQuantity) {
                    $qr->withSum(['warehouse_stocks as free_quantity' => function ($q) use ($request) {
                        $q->where('warehouse_id', $request->warehouse_id);
                    }], 'free_quantity');
                }

                $qr->withSum(['warehouse_stocks as sold_quantity' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'sold_quantity')
                ->withSum(['warehouse_stocks as wastage_quantity' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'wastage_quantity')
                ->withSum(['warehouse_stocks as transfer_out' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'transfer_out')
                ->withSum(['warehouse_stocks as transfer_in' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'transfer_in')
                ->withSum(['warehouse_stocks as available_quantity' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'available_quantity');
            });

        $data['total_product_price'] = $products->get()->map(function ($item) {
            $opening = floatval($item->opening_quantity ?? 0);
            $purchased = floatval($item->purchased_quantity ?? 0);
            $free = floatval($item->free_quantity ?? 0);
            $available = floatval($item->available_quantity ?? 0);
            $cost = floatval($item->product_cost ?? 0);

            $total_received = $opening + $purchased + $free;
            $paid_ratio = $total_received > 0 ? ($opening + $purchased) / $total_received : 0;

            return $available * $paid_ratio * $cost;
        })->sum();

        if (isset($request->print) && $request->print == 'excel') {
            $allStocks = $products->get();
            $data['products'] = $allStocks;
            $data['page_name'] = 'product-stocks';
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ReportExport($data), ('ProductWiseStockReport-'.\Carbon\Carbon::now()->format('Y-m-d').'.xlsx'));
        }

        $data['products'] = $products->paginate(30);

        return view('admin.reports.inventory-reports.product-stocks', $data);
    }

    public function productStockPdf(Request $request)
    {
        $data['selectproducts'] = Product::companies()->pluck('product_name', 'id');
        $data['categories']     = Category::companies()->with('subcategories')->get();
        $data['brands']         = Brand::companies()->pluck('name', 'id');
        $data['warehouses']     = Warehouse::companies()->pluck('name', 'id');
        $data['settings']       = SoftwareSetting::companies()->get();

        $hasFreeQuantity = \Illuminate\Support\Facades\Schema::hasColumn('product_stocks', 'free_quantity');

        $products = Product::companies()
            ->when($request->filled('brand_id'), function ($q) use($request) {
                $q->where('brand_id', $request->brand_id);
            })
            ->when($request->filled('category_id'), function ($q) use($request) {
                $q->where('fk_category_id', $request->category_id);
            })
            ->when($request->filled('fk_sub_category_id'), function ($q) use($request) {
                $q->where('fk_sub_category_id', $request->fk_sub_category_id);
            })
            ->when($request->filled('product_id'), function ($q) use($request) {
                $q->where('id', $request->product_id);
            })
            ->when($request->warehouse_id == 'all', function ($q) use ($request, $hasFreeQuantity) {
                $q->withSum('warehouse_stocks as opening_quantity', 'opening_quantity')
                    ->withSum('warehouse_stocks as purchased_quantity', 'purchased_quantity');
                
                if ($hasFreeQuantity) {
                    $q->withSum('warehouse_stocks as free_quantity', 'free_quantity');
                }

                $q->withSum('warehouse_stocks as sold_quantity', 'sold_quantity')
                    ->withSum('warehouse_stocks as wastage_quantity', 'wastage_quantity')
                    ->withSum('warehouse_stocks as transfer_out', 'transfer_out')
                    ->withSum('warehouse_stocks as transfer_in', 'transfer_in')
                    ->withSum('warehouse_stocks as available_quantity', 'available_quantity');

            })->when($request->warehouse_id != 'all', function ($qr) use ($request, $hasFreeQuantity) {

                $qr->withSum(['warehouse_stocks as opening_quantity' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'opening_quantity')
                ->withSum(['warehouse_stocks as purchased_quantity' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'purchased_quantity');

                if ($hasFreeQuantity) {
                    $qr->withSum(['warehouse_stocks as free_quantity' => function ($q) use ($request) {
                        $q->where('warehouse_id', $request->warehouse_id);
                    }], 'free_quantity');
                }

                $qr->withSum(['warehouse_stocks as sold_quantity' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'sold_quantity')
                ->withSum(['warehouse_stocks as wastage_quantity' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'wastage_quantity')
                ->withSum(['warehouse_stocks as transfer_out' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'transfer_out')
                ->withSum(['warehouse_stocks as transfer_in' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'transfer_in')
                ->withSum(['warehouse_stocks as available_quantity' => function ($q) use ($request) {
                    $q->where('warehouse_id', $request->warehouse_id);
                }], 'available_quantity');
            });

        $data['total_product_price'] = $products->get()->map(function ($item) {
            $opening = floatval($item->opening_quantity ?? 0);
            $purchased = floatval($item->purchased_quantity ?? 0);
            $free = floatval($item->free_quantity ?? 0);
            $available = floatval($item->available_quantity ?? 0);
            $cost = floatval($item->product_cost ?? 0);

            $total_received = $opening + $purchased + $free;
            $paid_ratio = $total_received > 0 ? ($opening + $purchased) / $total_received : 0;

            return $available * $paid_ratio * $cost;
        })->sum();

        $data['products'] = $products->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.inventory-reports.product-stocks-pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('Inventory_Report_' . now()->format('Y-m-d') . '.pdf');
    }

    public function syncStock(Request $request)
    {
        try {
            DB::transaction(function () {
                $company_id = companyId();
                $products = \App\Product::where('fk_company_id', $company_id)->get();
                $hasFreeQty = \Illuminate\Support\Facades\Schema::hasColumn('product_stocks', 'free_quantity');

                foreach ($products as $product) {
                    $pid = $product->id;

                    // Purchases
                    $purchases = DB::table('purchase_details')
                        ->join('purchases', 'purchases.id', '=', 'purchase_details.fk_purchase_id')
                        ->where('purchase_details.fk_product_id', $pid)
                        ->select('purchase_details.warehouse_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(free_quantity) as free_qty'))
                        ->groupBy('purchase_details.warehouse_id')
                        ->get();

                    // Sales
                    $sales = DB::table('sales_details')
                        ->join('sales', 'sales.id', '=', 'sales_details.fk_sales_id')
                        ->where('sales_details.fk_product_id', $pid)
                        ->select('sales_details.warehouse_id', DB::raw('SUM(quantity) as qty'))
                        ->groupBy('sales_details.warehouse_id')
                        ->get();

                    // Stock Transfers
                    $transfers_in = DB::table('stock_transfer_details')
                        ->join('stock_transfers', 'stock_transfers.id', '=', 'stock_transfer_details.stock_transfer_id')
                        ->where('stock_transfer_details.product_id', $pid)
                        ->select('stock_transfers.to_warehouse_id as warehouse_id', DB::raw('SUM(stock_transfer_details.quantity) as qty'))
                        ->groupBy('stock_transfers.to_warehouse_id')
                        ->get();

                    $transfers_out = DB::table('stock_transfer_details')
                        ->join('stock_transfers', 'stock_transfers.id', '=', 'stock_transfer_details.stock_transfer_id')
                        ->where('stock_transfer_details.product_id', $pid)
                        ->select('stock_transfers.from_warehouse_id as warehouse_id', DB::raw('SUM(stock_transfer_details.quantity) as qty'))
                        ->groupBy('stock_transfers.from_warehouse_id')
                        ->get();

                    // Global Adjustments (Applied to main showroom / null warehouse)
                    $sale_returns = \App\SaleReturnDetail::where('product_id', $pid)->sum('quantity');
                    $sale_exchanges = \App\SaleReturnExchange::where('product_id', $pid)->sum('quantity');
                    $purchase_returns = \App\PurchaseReturnDetail::where('product_id', $pid)->sum('quantity');
                    $damages = \App\Models\Inventory\ProductDamageItem::where('fk_product_id', $pid)->sum('quantity');

                    $stocks = \App\ProductStock::where('fk_product_id', $pid)->get();

                    foreach ($stocks as $stock) {
                        $wh = $stock->warehouse_id;

                        $p_qty = $purchases->where('warehouse_id', $wh)->sum('qty');
                        $f_qty = $purchases->where('warehouse_id', $wh)->sum('free_qty');
                        $s_qty = $sales->where('warehouse_id', $wh)->sum('qty');
                        
                        $t_in = $transfers_in->where('warehouse_id', $wh)->sum('qty');
                        $t_out = $transfers_out->where('warehouse_id', $wh)->sum('qty');

                        $sr_qty = 0;
                        $se_qty = 0;
                        $pr_qty = 0;
                        $d_qty = 0;

                        if ($wh == null) {
                            $sr_qty = $sale_returns;
                            $se_qty = $sale_exchanges;
                            $pr_qty = $purchase_returns;
                            $d_qty = $damages;
                        }

                        $opening = $stock->opening_quantity ?? 0;
                        
                        // Net values
                        $net_purchased = $p_qty - $pr_qty;
                        $net_sold = $s_qty + $se_qty - $sr_qty;

                        // Available = Opening + Net Purchased + Free + Transfer In - Transfer Out - Net Sold - Wastage
                        $available = $opening + $net_purchased + $f_qty + $t_in - $t_out - $net_sold - $d_qty;

                        $updateData = [
                            'purchased_quantity' => $net_purchased,
                            'sold_quantity'      => $net_sold,
                            'wastage_quantity'   => $d_qty,
                            'transfer_in'        => $t_in,
                            'transfer_out'       => $t_out,
                            'available_quantity' => $available
                        ];

                        if ($hasFreeQty) {
                            $updateData['free_quantity'] = $f_qty;
                        }

                        $stock->update($updateData);
                    }
                }
            });

            return back()->withSuccess('All Stock Data Successfully Synchronized & Rebuilt!');
        } catch (\Exception $e) {
            return back()->withError('Error syncing stock: ' . $e->getMessage());
        }
    }
}