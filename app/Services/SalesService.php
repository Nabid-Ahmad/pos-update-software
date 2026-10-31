<?php

namespace App\Services;

use App\Customer;
use App\CustomerPricing;
use App\marketer;
use App\marketers_details;
use App\marketing;
use App\Models\CustomerSupplier;
use App\Product;
use App\ProductSerial;
use App\ProductSerialSalesDetails;
use App\ProductStock;
use App\PurchaseDetails;
use App\Sale;
use App\SaleDistributor;
use App\Services\Reports\CustomerReportServices;
use App\SoftwareSetting;
use App\Transaction;
use Carbon\Carbon;

class SalesService
{
    public $sale;
    private $request;

    public function createInvoice()
    {
        $this->request = request();

        $sub_total = 0;
        if ($this->request->product_sub_total && is_array($this->request->product_sub_total)) {
            $sub_total = array_sum($this->request->product_sub_total);
        } else {
            $sub_total = $this->request->sub_total ?? $this->request->payable_amount ?? 0;
        }

        $data = [
            'sale_date'      => $this->request->sale_date ?? date('Y-m-d'),
            'sale_reference' => $this->request->sale_reference,
            'sub_total'      => $sub_total,
            'previous_due'   => floatval($this->request->previous_due ?? 0),
            'sale_type'      => $this->request->sale_type ?? 'Retail',
        ];

        $todaySale = Sale::whereSale_date($data['sale_date'])->latest('created_at')->first();
        if (!$todaySale) {
            $data['sale_invoice_id'] = Carbon::now()->format('Ymd') . '001';
        } else {
            $data['sale_invoice_id'] = Sale::max('sale_invoice_id') + 1;
        }

        $data['fk_customer_id'] = $this->request->fk_customer_id ?? $this->request->defaultCustomer;
        $data['marketers_id']   = $this->request->marketers_id ?? null;
        $data['supplier_id']    = $this->request->supplier_id ?? null;
        $data['note']           = $this->request->note ?? null;

        // ✅ FIX: Discount - percent অথবা flat সঠিকভাবে save করো
        $discount_percent = floatval($this->request->discount_percent ?? 0);
        $discount_flat    = floatval($this->request->invoice_discount ?? 0);

        if ($discount_percent > 0) {
            $data['invoice_discount']  = $discount_percent; // % value save
            $data['discount_type']     = '%';
        } else {
            $data['invoice_discount']  = $discount_flat; // flat amount save
            $data['discount_type']     = 'flat';
        }

        // ✅ FIX: Special Discount - percent অথবা flat সঠিকভাবে save করো
        $special_percent = floatval($this->request->special_discount_percent ?? 0);
        $special_flat    = floatval($this->request->special_discount_amount ?? 0);

        if ($special_percent > 0) {
            $data['special_discount']         = $special_percent; // % value save
            $data['special_discount_type']    = '%';
        } else {
            $data['special_discount']         = $special_flat; // flat amount save
            $data['special_discount_type']    = 'flat';
        }

        $data['invoice_tax']     = floatval($this->request->invoice_tax ?? 0);
        $data['cod_amount']      = floatval($this->request->cod_amount ?? 0);
        $data['advanced']        = floatval($this->request->advanced_payment ?? 0);
        $data['receive_amount']  = floatval($this->request->receive_amount ?? 0);
        $data['change_amount']   = floatval($this->request->change_amount ?? 0);
        $data['warehouse_id']    = ($this->request->warehouse_id && $this->request->warehouse_id != 0) ? $this->request->warehouse_id : null;
        $data['delivery_charge'] = floatval($this->request->delivery_charge ?? 0);
        $data['currier_amount']  = floatval($this->request->delivery_charge ?? 0); // Sync currier_amount column for invoice/report
        $data['delivery_charge_payer'] = $this->request->delivery_charge_payer ?? 'company';
        $data['total_payable']   = floatval($this->request->total_payable ?? $this->request->payable_amount ?? 0);

        $this->sale = Sale::create($data);

        // Auto Expense (Voucher) for Company Delivery Charge
        try {
            if ($data['delivery_charge_payer'] == 'company' && $data['delivery_charge'] > 0) {
                // Try to find a relevant chart of account
                $chart = \App\AccountChart::where('chart_name', 'LIKE', '%Delivery%')
                    ->orWhere('chart_name', 'LIKE', '%Carrying%')
                    ->first();
                
                \App\Voucher::create([
                    'voucher_date' => $data['sale_date'] ?? now()->format('Y-m-d'),
                    'voucher_type' => 'debit',
                    'amount' => $data['delivery_charge'],
                    'fk_account_id' => $this->request->fk_account_id, // Use same account as sale
                    'fk_account_chart_id' => $chart ? $chart->id : null,
                    'description' => 'Auto Delivery Charge for Sale Invoice: ' . ($this->sale->invoiceId ?? 'INV-'.$this->sale->id),
                    'fk_company_id' => companyId(),
                    'created_by' => auth()->id(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create auto delivery voucher: ' . $e->getMessage());
        }
    }

    public function updateSalesTotalAmount()
    {
        $total_sales_amount = $this->getTotalSalesAmount();

        // ✅ FIX: First discount calculate
        if ($this->sale->discount_type == 'flat') {
            $first_discount = $this->sale->invoice_discount;
        } else {
            $first_discount = $total_sales_amount * ($this->sale->invoice_discount / 100);
        }

        // ✅ FIX: Compound - বাকি টাকার উপর special discount
        $remaining = $total_sales_amount - $first_discount;
        if ($this->sale->special_discount_type == 'flat') {
            $special_discount = $this->sale->special_discount;
        } else {
            $special_discount = $remaining * ($this->sale->special_discount / 100);
        }

        // Include courier charge in payable total if paid by customer
        $courier_charge = 0;
        if ($this->sale->delivery_charge_payer == 'customer') {
            $courier_charge = floatval($this->sale->currier_amount ?? $this->sale->delivery_charge ?? 0);
        }

        $total_payable = $total_sales_amount + $this->sale->invoice_tax + $this->sale->cod_amount + $courier_charge - $first_discount - $special_discount;

        $receive_amount = floatval($this->sale->receive_amount);
        $change_amount  = floatval($this->request->change_amount ?? 0);
        $net_paid       = $receive_amount - $change_amount;
        $previous_due   = floatval($this->sale->previous_due);
        $advanced       = floatval($this->sale->advanced);

        // Standardize balance calculation: Positive = Outstanding Due (matching Package Sale Service)
        $balance = ($previous_due + $total_payable) - ($net_paid + $advanced);

        // ✅ FIX: invoice_discount আর special_discount আলাদা আলাদা save করো
        $this->sale->update([
            'sub_total'       => $total_sales_amount,
            'invoice_discount'=> $first_discount,   // শুধু প্রথম discount এর amount
            'special_discount'=> $special_discount,  // শুধু special discount এর amount
            'total_payable'   => $total_payable,
            'paid_amount'     => $net_paid,
            'balance'         => $balance,
            'change_amount'   => $change_amount,
        ]);
    }

    public function customer_supplier()
    {
        $request = request();
        if ($request->fk_customer_id != null && $request->supplier_id != null) {
            // Skip tracking supplier balances for default Cash Customers to prevent mismatch accumulation
            $customer = Customer::find($request->fk_customer_id);
            if ($customer && $customer->default == 1) {
                return;
            }

            $service = new \App\Services\Reports\CustomerReportServices();
            $customerBalances = (object)$service->getCustomersBalances(collect([$customer]), null, $request->supplier_id);
            $current_balance1 = $customerBalances->sum('balance');
            
            $record = CustomerSupplier::where('customer_id', $request->fk_customer_id)->where('supplier_id', $request->supplier_id)->first();
            if ($record) {
                $record->update(['balance' => $current_balance1]);
            } else {
                CustomerSupplier::create(['customer_id' => $request->fk_customer_id, 'supplier_id' => $request->supplier_id, 'balance' => $current_balance1, 'opening_balance' => 0]);
            }
        }
    }

    public function addSaleCurrier()
    {
        if ($this->request->currier_amount != '') {
            $this->sale->update(['currier_id' => $this->request->currier_id, 'currier_amount' => $this->request->currier_amount]);
        }
        $delivery_charge = (float)($this->request->delivery_charge ?? 0);
        if ($delivery_charge > 0) {
            Transaction::create([
                'fk_company_id' => companyId(), 'fk_created_by' => auth()->id(), 'fk_updated_by' => auth()->id(),
                'fk_account_id' => $this->request->fk_account_id ?? defaultAccount()->id, 'amount' => -abs($delivery_charge),
                'transactionable_type' => Sale::class, 'transactionable_id' => $this->sale->id, 'date' => $this->sale->sale_date,
            ]);
        }
    }

    public function addMetaSale()
    {
        if (ltrim($this->request->receiver ?? '') || ltrim($this->request->deliverer ?? '')) {
            $this->sale->saleMeta()->create([
                'received_by'  => $this->request->received_by ?: (ltrim($this->request->receiver ?? '') ? $this->getReceiverId() : null),
                'delivered_by' => $this->request->delivered_by ?: (ltrim($this->request->deliverer ?? '') ? $this->getDelivererId() : null)
            ]);
        }
    }

    public function storeSaleDetails()
    {
        $isUpdateCustomerSalePrice = $this->isUpdateCustomerSalePrice();
        foreach ($this->request->product_names as $product_id => $name) {
            $qty = $this->request->quantities[$product_id];
            $price = $this->request->unit_prices[$product_id];
            $discount_percent = floatval($this->request->product_discounts[$product_id] ?? 0);
            $base_subtotal = $qty * $price;
            $discount_amount = $base_subtotal * ($discount_percent / 100);

            $details = $this->sale->sale_details()->create([
                'fk_product_id'    => $product_id,
                'warehouse_id'     => null,
                'quantity'         => $qty,
                'unit_price'       => $price,
                'product_sub_total'=> ($base_subtotal - $discount_amount),
                'product_item_ids' => $this->request->product_item_ids[$product_id] ?? '',
                'product_cost'     => $this->getProductCost($product_id) ?? $price,
                'product_discount' => $discount_percent,
            ]);
            $this->updateProductStock($product_id, $product_id);
            if ($isUpdateCustomerSalePrice) { $this->updateCustomerSalePrice($product_id, $product_id); }
            if ($this->request->has_serials[$product_id] == "1") { $this->storeSaleSerial($details->id, $this->request->product_serials[$product_id]); }
        }
    }




    

    public function makeCustomerPayment()
    {
        $receive_amount = floatval($this->request->receive_amount ?? 0);
        $change_amount  = floatval($this->request->change_amount ?? 0);
        $amount         = $receive_amount - $change_amount;
        if ($amount > 0) {
            $this->sale->payments()->create([
                'fk_account_id' => $this->request->fk_account_id ?? defaultAccount()->id,
                'fk_company_id' => companyId(),
                'date'          => $this->sale->sale_date,
                'amount'        => $amount
            ]);
        }
    }

    public function updateCustomerBalance($customer_id, $supplier_id = null)
    {
        $service = new CustomerReportServices();
        $customerBalances = (object)$service->getCustomersBalances(Customer::where('id', $customer_id)->take(1)->get());
        Customer::find($customer_id)->update(['current_balance' => $customerBalances->sum('balance')]);
    }

    public function updateCustomerSupplierBalanceByDelta($customer_id, $supplier_id, $delta)
    {
        if (!$customer_id || !$supplier_id || $delta == 0) return;
        
        $customer = Customer::find($customer_id);
        if ($customer && $customer->default == 1) {
            return;
        }

        $record = CustomerSupplier::where('customer_id', $customer_id)->where('supplier_id', $supplier_id)->first();
        if ($record) {
            CustomerSupplier::where('customer_id', $customer_id)->where('supplier_id', $supplier_id)->update(['balance' => $record->balance + $delta]);
        }
    }

    public function distributePaymentToSales($customer_id, $amount)
    {
        $amount = abs((float)$amount);
        if ($amount <= 0) return;

        $sales = Sale::where('fk_customer_id', $customer_id)
            ->orderBy('sale_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($sales as $sale) {
            $due = $sale->due; // Relies on getPaidAttribute which now includes adjusted_paid
            if ($due > 0) {
                $pay_amount = min($due, $amount);
                $sale->adjusted_paid += $pay_amount;
                $sale->save();
                
                $amount -= $pay_amount;
                if ($amount <= 0) {
                    break;
                }
            }
        }
    }

    public function isAutoAdjustSaleDueEnabled($customer_id = null)
    {
        $companyId = auth()->check() ? companyId() : null;
        if (!$companyId && $customer_id) {
            $customer = Customer::find($customer_id);
            $companyId = optional($customer)->fk_company_id;
        }

        if (!$companyId) {
            return false;
        }

        return SoftwareSetting::where('fk_company_id', $companyId)
            ->where('title', 'Auto Adjust Sale Due From Collection')
            ->where('options', 'yes')
            ->exists();
    }

    public function syncCustomerSalesDue($customer_id)
    {
        if (!$customer_id) return;

        if (!$this->isAutoAdjustSaleDueEnabled($customer_id)) {
            return;
        }

        // Reset adjusted_paid to 0 for all sales of this customer
        Sale::where('fk_customer_id', $customer_id)->update(['adjusted_paid' => 0]);

        // Get total approved collections for this customer
        $totalCollection = \App\AccountReview::where('transactionable_type', Customer::class)
            ->where('transactionable_id', $customer_id)
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->sum(\DB::raw('ABS(amount)'));

        if ($totalCollection > 0) {
            $this->distributePaymentToSales($customer_id, $totalCollection);
        }
    }

    public function syncAllCustomersSalesDue($company_id = null)
    {
        $query = Sale::query();
        if ($company_id) {
            $query->where('fk_company_id', $company_id);
        }
        $customerIds = $query->distinct()->pluck('fk_customer_id');
        foreach ($customerIds as $customerId) {
            if ($customerId) {
                $this->syncCustomerSalesDue($customerId);
            }
        }
    }

    public function resetAllCustomersSalesDue($company_id = null)
    {
        $query = Sale::query();
        if ($company_id) {
            $query->where('fk_company_id', $company_id);
        }
        $query->update(['adjusted_paid' => 0]);
    }

    public function marketers_commission()
    {
        $request = request();
        try {
            if ($request->marketers_id != '') {
                $marketers_details = marketers_details::where('start_amount', '<=', $request->payable_amount)->where('end_amount', '>=', $request->payable_amount)->first();
                $percantage = $marketers_details ? $marketers_details->marketers_commission : 0;
                $marketers_amount  = round(($request->payable_amount * $percantage) / 100);
                marketer::where('id', $request->marketers_id)->increment('balance', $marketers_amount);
            }
        } catch (\Exception $ex) { return; }
    }

    private function getReceiverId() { return SaleDistributor::create(['name' => $this->request->receiver, 'type' => 'receiver'])->id ?? 1; }
    private function getDelivererId() { return SaleDistributor::create(['name' => $this->request->deliverer, 'type' => 'deliverer'])->id ?? 1; }
    private function isUpdateCustomerSalePrice() { return optional(SoftwareSetting::companies()->where('title', 'Seller Sale Price Update From Sale')->where('options', 'yes')->first())->options ? true : false; }
    private function getProductCost($product_id) { 
        return optional(Product::find($product_id))->product_cost ?? 0;
    }

    private function updateProductStock($productId, $key)
    {
        $warehouseId = $this->request->warehouse_id ?: optional(\App\Warehouse::companies()->first())->id ?: null;
        $productStock = ProductStock::where('fk_product_id', $productId)->where('fk_company_id', companyId())->where('warehouse_id', $warehouseId)->first();
        
        if ($productStock) {
            $productStock->update([
                'sold_quantity'      => ($productStock->sold_quantity + $this->request->quantities[$key]),
                'available_quantity' => ($productStock->available_quantity - $this->request->quantities[$key])
            ]);
        } else {
            ProductStock::create([
                'fk_product_id'      => $productId,
                'fk_company_id'      => companyId(),
                'opening_quantity'   => 0,
                'available_quantity' => (0 - $this->request->quantities[$key]),
                'sold_quantity'      => $this->request->quantities[$key],
                'warehouse_id'       => $warehouseId
            ]);
        }
    }

    private function updateCustomerSalePrice($product_id, $key)
    {
        CustomerPricing::updateOrCreate(
            ['customer_id' => $this->request->fk_customer_id ?? $this->request->defaultCustomer, 'product_id' => $product_id],
            ['price' => $this->request->unit_prices[$key]]
        );
    }

    private function getTotalSalesAmount()
    {
        return $this->sale->sale_details->sum(function ($item) {
            $base = $item->quantity * $item->unit_price;
            $discount_amount = $base * (floatval($item->product_discount) / 100);
            return $base - $discount_amount;
        });
    }

    private function storeSaleSerial($sale_detail_id, $serials)
    {
        foreach ($serials as $serial) {
            ProductSerial::where('serial', $serial)->update(['is_sold' => 1, 'sale_id' => $this->sale->id]);
            ProductSerialSalesDetails::create([
                'sales_details_id'  => $sale_detail_id,
                'product_serial_id' => ProductSerial::where('serial', $serial)->first()->id
            ]);
        }
    }
}