<?php

namespace App\Http\Requests;

use App\Models\Inventory\ProductDamage;
use App\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ProductDamageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date'         => 'required|date',
            'product_id'   => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity'     => 'required|array|min:1',
            'quantity.*'   => 'required|numeric|min:0.01',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $productIds = $this->input('product_id', []);
            $quantities = $this->input('quantity', []);

            $filteredIds = array_filter($productIds);
            if (count($filteredIds) !== count(array_unique($filteredIds))) {
                $validator->errors()->add('product_id', 'The same product cannot be selected multiple times in one damage entry.');
            }

            foreach ($productIds as $key => $productId) {
                if (!$productId) {
                    $validator->errors()->add("product_id.{$key}", 'Please select a product.');
                    continue;
                }

                $damageQty = floatval($quantities[$key] ?? 0);
                if ($damageQty <= 0) {
                    $validator->errors()->add("quantity.{$key}", 'Damaged quantity must be greater than 0.');
                    continue;
                }

                $product = Product::find($productId);
                $productName = $product ? $product->product_name : "Product #{$productId}";

                $stock = \App\ProductStock::where('fk_company_id', companyId())
                    ->where('fk_product_id', $productId)
                    ->first();

                $availableQty = floatval($stock->available_quantity ?? 0);

                if ($availableQty <= 0) {
                    $validator->errors()->add(
                        "quantity.{$key}",
                        "Cannot record damage for '{$productName}'. Available stock is 0!"
                    );
                } elseif ($damageQty > $availableQty) {
                    $validator->errors()->add(
                        "quantity.{$key}",
                        "Product '{$productName}' damaged quantity ({$damageQty}) exceeds available stock ({$availableQty})!"
                    );
                }
            }
        });
    }

    public function persist()
    {

        return DB::transaction(function () {
            $invoice = ProductDamage::create([
                'date'      => $this->date,
                'reference' => $this->reference,
                'amount'    => 0,
            ]);

            $this->makeWastage($invoice);
            $this->updateInvoiceTotal($invoice);

        });
    }

    private function updateInvoiceTotal($invoice)
    {
        $totalAmount = $invoice->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
        $invoice->update(['amount' => $totalAmount]);
    }

    private function makeWastage(ProductDamage $invoice)
    {
        foreach ($this->product_id as $key => $product_id){
            $damagedItem = $invoice->items()->create([
                'fk_product_id' => $product_id,
                'description'   => $this->description[$key] ?? null,
                'type'          => $this->type[$key] ?? 'damaged',
                'quantity'      => $this->quantity[$key],
                'price'         => Product::find($product_id)->product_cost
            ]);

            $wastage = $damagedItem->wastage()->create([
                'wastagable_type'   => ProductDamage::class,
                'wastagable_id'     => $invoice->id,
                'quantity'          => $this->quantity[$key]
            ]);
            $wastage->increaseWastageStock();

        }
    }
}
