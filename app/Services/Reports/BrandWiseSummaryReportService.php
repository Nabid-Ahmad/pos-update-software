<?php

namespace App\Services\Reports;

use App\Brand;
use App\Sale;

class BrandWiseSummaryReportService
{
    public function getBrandWiseSummaryReportData()
    {
        $request = request();

        $data['brands'] = Brand::companies()->pluck('name', 'id');

        $start_date = $request->start_date ?? date('Y-m-d');
        $end_date = $request->end_date ?? date('Y-m-d');

        $sales = Sale::companies()->with('sale_details', 'sale_details.product', 'sale_details.product.brand')
            ->whereBetween('sale_date', [$start_date, $end_date])
            ->get();

        $brandSummaries = [];

        foreach ($sales as $sale) {
            // Calculate actual sub total by safely parsing product_sub_total
            $actual_sub_total = 0;
            foreach ($sale->sale_details as $detail) {
                $val = str_replace(',', '', $detail->product_sub_total);
                // Use abs() so that positive and negative values (like in exchanges) don't cancel out to 0
                $actual_sub_total += abs((float)$val);
            }

            $invoice_sub_total = $actual_sub_total > 0 ? $actual_sub_total : 1;
            $paid_amount = (float)str_replace(',', '', $sale->paid_amount);
            $due_amount = (float)str_replace(',', '', $sale->total_payable) - $paid_amount;

            foreach ($sale->sale_details as $detail) {
                if ($detail->product && $detail->product->brand_id) {
                    $brandId = $detail->product->brand_id;
                    $brandName = $detail->product->brand->name ?? 'Unknown Brand';

                    if ($request->filled('fk_brand_id') && $brandId != $request->fk_brand_id) {
                        continue;
                    }

                    if (!isset($brandSummaries[$brandId])) {
                        $brandSummaries[$brandId] = [
                            'brand_name' => $brandName,
                            'total_sale' => 0,
                            'total_collection' => 0,
                            'total_due' => 0,
                        ];
                    }

                    $product_sub_total = (float)str_replace(',', '', $detail->product_sub_total);
                    $ratio = $product_sub_total / $invoice_sub_total;

                    $proportional_discount = $ratio * ((float)$sale->invoice_discount + (float)$sale->special_discount);
                    $net_sale_price = $product_sub_total - $proportional_discount;

                    $proportional_paid = $ratio * $paid_amount;
                    $proportional_due = $ratio * $due_amount;

                    $brandSummaries[$brandId]['total_sale'] += $net_sale_price;
                    $brandSummaries[$brandId]['total_collection'] += $proportional_paid;
                    $brandSummaries[$brandId]['total_due'] += $proportional_due;
                }
            }
        }

        $data['brandSummaries'] = $brandSummaries;
        return $data;
    }
}
