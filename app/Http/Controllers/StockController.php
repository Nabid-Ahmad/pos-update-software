<?php

namespace App\Http\Controllers;

use App\Product;
use App\ProductStock;
use App\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class StockController extends Controller
{


    public function report(Request $request)
    {
        $data['suppliers'] = Supplier::companies()->pluck('name', 'id');

        $report = $this->getReport($request);
        
        // DB level calculation to avoid loading thousands of models into RAM
        $queryForTotal = clone $report['stocks'];
        $data['totalProductPrice'] = DB::query()
            ->fromSub($queryForTotal, 'sub')
            ->join('products', 'sub.fk_product_id', '=', 'products.id')
            ->sum(DB::raw('products.product_cost * sub.available_quantity'));

        if (isset($request->print) && $request->print == 'excel') {
            // Excel needs all data
            $allStocks = $report['stocks']->get();
            $data['stocks']   = $allStocks;
            $data['supplier'] = $report['supplier'];
            $data['page_name'] = 'supplier-wise-stock';
            return Excel::download(new ReportExport($data), ('SupplierWiseStockReport-'.Carbon::now()->format('Y-m-d').'.xlsx'));
        }

        // Native DB pagination to save memory
        $data['stocks'] = $report['stocks']->paginate(30)->appends($request->query());
        $data['supplier']  = $report['supplier'];

        return view('admin.reports.supplier_wise_stock', $data);
    }


    public function stockPdf(Request $request)
    {
        $stocks   = $this->getReport($request)['stocks'];
        $supplier = $this->getReport($request)['supplier'];
        $suppliers = Supplier::companies()->pluck('name', 'id');

        $queryForTotal = clone $stocks;
        $totalProductPrice = DB::query()
            ->fromSub($queryForTotal, 'sub')
            ->join('products', 'sub.fk_product_id', '=', 'products.id')
            ->sum(DB::raw('products.product_cost * sub.available_quantity'));

        $data = [
            'stocks'            => $stocks->get(),
            'supplier'          => $supplier,
            'suppliers'         => $suppliers,
            'totalProductPrice' => $totalProductPrice,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.supplier_wise_stock_pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('Supplier_Wise_Stock_Report_' . now()->format('Y-m-d') . '.pdf');
    }


    public function stockPrint()
    {
        $request = request();
        $stocks = $this->getReport($request)['stocks'];
        $supplier = $this->getReport($request)['supplier'];
        $suppliers = \App\Supplier::companies()->pluck('name', 'id');
        
        $queryForTotal = clone $stocks;
        $totalProductPrice = \Illuminate\Support\Facades\DB::query()
            ->fromSub($queryForTotal, 'sub')
            ->join('products', 'sub.fk_product_id', '=', 'products.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('products.product_cost * sub.available_quantity'));

        return view('admin.reports.supplier_wise_stock_print', [
            'stocks' => $stocks->get(),
            'supplier' => $supplier,
            'suppliers' => $suppliers,
            'totalProductPrice' => $totalProductPrice
        ]);
    }


    private function getReport($request)
    {
        $stocks = ProductStock::companies()->whereHas('stock_product')->with('stock_product', 'product', 'product.category_name', 'product.supplier');

        $hasFreeQuantity = \Illuminate\Support\Facades\Schema::hasColumn('product_stocks', 'free_quantity');
        $freeQtySelect = $hasFreeQuantity ? "SUM(free_quantity)       as free_quantity," : "0       as free_quantity,";

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $stocks->select('fk_product_id', DB::raw("
                (SELECT COALESCE(SUM(quantity), 0) FROM purchase_details JOIN purchases ON purchases.id = purchase_details.fk_purchase_id WHERE purchase_details.fk_product_id = product_stocks.fk_product_id AND purchases.purchase_date BETWEEN '{$start_date}' AND '{$end_date}') as purchased_quantity,
                (SELECT COALESCE(SUM(free_quantity), 0) FROM purchase_details JOIN purchases ON purchases.id = purchase_details.fk_purchase_id WHERE purchase_details.fk_product_id = product_stocks.fk_product_id AND purchases.purchase_date BETWEEN '{$start_date}' AND '{$end_date}') as free_quantity,
                (SELECT COALESCE(SUM(quantity), 0) FROM sales_details JOIN sales ON sales.id = sales_details.fk_sales_id WHERE sales_details.fk_product_id = product_stocks.fk_product_id AND sales.sale_date BETWEEN '{$start_date}' AND '{$end_date}') as sold_quantity,
                (SELECT COALESCE(SUM(quantity), 0) FROM product_damage_items JOIN product_damages ON product_damages.id = product_damage_items.product_damage_id WHERE product_damage_items.fk_product_id = product_stocks.fk_product_id AND product_damages.date BETWEEN '{$start_date}' AND '{$end_date}') as wastage_quantity,
                SUM(transfer_in) as transfer_in,
                SUM(transfer_out) as transfer_out,
                (
                    SUM(available_quantity)
                    - (SELECT COALESCE(SUM(quantity + free_quantity), 0) FROM purchase_details JOIN purchases ON purchases.id = purchase_details.fk_purchase_id WHERE purchase_details.fk_product_id = product_stocks.fk_product_id AND purchases.purchase_date >= '{$start_date}')
                    + (SELECT COALESCE(SUM(quantity), 0) FROM sales_details JOIN sales ON sales.id = sales_details.fk_sales_id WHERE sales_details.fk_product_id = product_stocks.fk_product_id AND sales.sale_date >= '{$start_date}')
                    + (SELECT COALESCE(SUM(quantity), 0) FROM product_damage_items JOIN product_damages ON product_damages.id = product_damage_items.product_damage_id WHERE product_damage_items.fk_product_id = product_stocks.fk_product_id AND product_damages.date >= '{$start_date}')
                ) as opening_quantity,
                (
                    SUM(available_quantity)
                    - (SELECT COALESCE(SUM(quantity + free_quantity), 0) FROM purchase_details JOIN purchases ON purchases.id = purchase_details.fk_purchase_id WHERE purchase_details.fk_product_id = product_stocks.fk_product_id AND purchases.purchase_date > '{$end_date}')
                    + (SELECT COALESCE(SUM(quantity), 0) FROM sales_details JOIN sales ON sales.id = sales_details.fk_sales_id WHERE sales_details.fk_product_id = product_stocks.fk_product_id AND sales.sale_date > '{$end_date}')
                    + (SELECT COALESCE(SUM(quantity), 0) FROM product_damage_items JOIN product_damages ON product_damages.id = product_damage_items.product_damage_id WHERE product_damage_items.fk_product_id = product_stocks.fk_product_id AND product_damages.date > '{$end_date}')
                ) as available_quantity
            "));
        } else {
            $stocks->select('fk_product_id', DB::raw("
                SUM(purchased_quantity)  as purchased_quantity,
                SUM(sold_quantity)       as sold_quantity,
                SUM(wastage_quantity)    as wastage_quantity,
                {$freeQtySelect}
                SUM(transfer_in)         as transfer_in,
                SUM(transfer_out)        as transfer_out,
                SUM(opening_quantity)    as opening_quantity,
                SUM(available_quantity)  as available_quantity
            "));
        }

        $stocks->groupBy('fk_product_id');

        $supplier = '';

        if ($request->filled('fk_supplier_id')) {
            $stocks->whereHas('product', function ($q) use ($request) {
                $q->where('supplier_id', $request->fk_supplier_id);
            });

            $supplier = Supplier::findOrFail($request->fk_supplier_id);
        }

        return [
            'stocks' => $stocks,
            'supplier' => $supplier
        ];
    }

    public function getProductSerials(Request $request)
    {
        $product = Product::with(['product_serials' => function($q) use($request) {
            $q
            // ->where('is_sold', 0)
            ->where('warehouse_id', $request->warehouse_id);
        }])->where('id', $request->product_id)->first();

        return view('admin.reports.inventory-reports.stock-roduct-serials', compact('product'));
//        return $product;
//        return $request->all();
    }
}