<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProductStock;
use Auth;
use DB;

class ProductAlertController extends Controller
{
    public function index()
    {
        $product_stocks = ProductStock::with('stock_product')
            ->where('fk_company_id', companyId())
            ->whereHas('stock_product', function ($q){
                $q->whereColumn('product_alert_quantity', '>=', 'available_quantity');
            })
            ->get();


        return view('admin.reports.product_alert', [
            'product_stocks' => $product_stocks
        ]);
    }

    public function ajaxAlert()
    {
        $product_stocks = ProductStock::with('stock_product:id,product_name,product_code')
            ->where('fk_company_id', companyId())
            ->whereHas('stock_product', function ($q){
                $q->whereColumn('product_alert_quantity', '>=', 'available_quantity');
            })
            ->take(10)
            ->get(['id', 'fk_product_id', 'available_quantity']);

        return response()->json([
            'count' => $product_stocks->count(),
            'products' => $product_stocks
        ]);
    }
}
