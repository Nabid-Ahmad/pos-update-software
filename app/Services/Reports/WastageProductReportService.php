<?php


namespace App\Services\Reports;


use App\Customer;
use App\Models\Inventory\Wastage;
use App\Supplier;
use Illuminate\Support\Facades\DB;

class WastageProductReportService
{

    public function getWastageProductData($request)
    {
        $data['customers'] = Customer::companies()->pluck('name', 'id');
        $data['suppliers'] = Supplier::companies()->pluck('name', 'id');

        $wastages = Wastage::whereHasMorph('itemable', [\App\Models\RandomReturnItem::class, \App\Models\Inventory\ProductDamageItem::class], function($query) {
            $query->whereHas('product', function($product) {
                $product->where('fk_company_id', companyId());
            });
        })->when ($request->filled('from') && $request->filled('to'), function ($q) use($request) {
            $q->whereBetween(DB::raw('DATE(created_at)'), [$request->from, $request->to]);
        })->when($request->filled('fk_customer_id'), function ($q) use($request) {
            $q->whereHasMorph('itemable', [\App\Models\RandomReturnItem::class, \App\Models\Inventory\ProductDamageItem::class], function($qr, $type) use($request) {
                if ($type === \App\Models\RandomReturnItem::class) {
                    $qr->whereHas('randomReturn', function($qrr) use($request) {
                        $qrr->where('fk_customer_id', $request->fk_customer_id);
                    });
                } else if ($type === \App\Models\Inventory\ProductDamageItem::class) {
                    $qr->whereHas('product_damage', function($qrr) use($request) {
                        $qrr->whereIn('id', function($q_in) use ($request) {
                            $q_in->select('product_damage_id')
                                 ->from('sale_return_details')
                                 ->join('sale_returns', 'sale_return_details.sale_return_id', '=', 'sale_returns.id')
                                 ->where('sale_returns.customer_id', $request->fk_customer_id)
                                 ->whereNotNull('product_damage_id');
                        });
                    });
                }
            });
        })->when($request->filled('fk_supplier_id'), function ($q) use($request) {
            $q->whereHasMorph('itemable', [\App\Models\RandomReturnItem::class, \App\Models\Inventory\ProductDamageItem::class], function($qr) use($request) {
                $qr->whereHas('product', function($qrr) use($request) {
                    $qrr->where('supplier_id', $request->fk_supplier_id);
                });
            });
        });

        $wastages = $wastages->orderBy('id', 'desc');
        
        $fullWastage = clone $wastages;

        $data['wastages']      = $wastages->paginate(30);
        $data['wastagesFull']   = $fullWastage->get();

        return $data;
    }
}
