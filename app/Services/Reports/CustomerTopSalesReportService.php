<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;
use App\Customer;
use Carbon\Carbon;

class CustomerTopSalesReportService
{
    public function getReportData($request)
    {
        $start_date = $request->start_date ?? Carbon::parse(now())->subDays(30)->format('Y-m-d');
        $end_date   = $request->end_date ?? Carbon::parse(now())->format('Y-m-d');
        $order_by   = $request->order_by ?? 'amount'; // amount or quantity
        $customer_id= $request->customer_id ?? '';
        $supplier_id= $request->supplier_id ?? '';

        $query = DB::table('sales')
            ->join('customers', 'sales.fk_customer_id', '=', 'customers.id')
            ->join('sales_details', 'sales.id', '=', 'sales_details.fk_sales_id')
            ->join('products', 'sales_details.fk_product_id', '=', 'products.id')
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.customer_code',
                DB::raw('SUM(sales_details.quantity) as total_quantity'),
                DB::raw('SUM(sales_details.product_sub_total) as total_amount'),
                DB::raw('COUNT(DISTINCT sales.id) as total_invoices')
            )
            ->where('sales.fk_company_id', companyId())
            ->whereBetween('sales.sale_date', [$start_date, $end_date]);

        if (!empty($customer_id)) {
            $query->where('sales.fk_customer_id', $customer_id);
        }

        if (!empty($supplier_id)) {
            $query->where('products.supplier_id', $supplier_id);
        }

        $query->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.customer_code');

        if ($order_by == 'quantity') {
            $query->orderBy('total_quantity', 'desc');
        } else {
            $query->orderBy('total_amount', 'desc');
        }

        $reports = $query->paginate(50);
        $customers = Customer::companies()->get(['name', 'id', 'phone']);
        $suppliers = \App\Supplier::companies()->get(['name', 'id']);

        return compact('reports', 'start_date', 'end_date', 'order_by', 'customers', 'customer_id', 'suppliers', 'supplier_id');
    }
}
