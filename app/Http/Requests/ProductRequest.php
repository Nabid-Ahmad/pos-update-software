<?php

namespace App\Http\Requests;

use App\Product;
use App\Traits\FileSaver;
use Illuminate\Support\Facades\DB;
use App\Services\Products\ProductService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class ProductRequest extends FormRequest
{
    private $service;

    use FileSaver;
    public function __construct(ProductService $productService)
    {
        $this->service = $productService;
    }
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        $productId = request()->segment(3); 

        return [
            'product_name' => [
                'required',
                Rule::unique('products', 'product_name')->ignore($productId)->whereNull('deleted_at')->where('fk_company_id', companyId()),
            ],
            'product_code' => [
                'nullable',
                Rule::unique('products', 'product_code')->ignore($productId)->whereNull('deleted_at')->where('fk_company_id', companyId()),
            ],
            'fk_category_id'     => 'required|not_in:0',
            'fk_product_unit_id' => 'required|not_in:0',
            'product_cost'       => 'required|numeric',
            'product_price'      => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'product_name.required'         => 'Product name can not be empty',
            'product_name.unique'           => 'Product already exist for this company',
            'product_code.unique'           => 'Product code already exist',
            'fk_category_id.required'       => 'Category can not be empty',
            'fk_product_unit_id.required'   => 'Unit can not be empty',
            'product_cost.required'         => 'Purchase price can not be empty',
            'product_price.required'        => 'Sales price can not be empty',
        ];
    }

    public function storeProduct()
    {
        DB::transaction(function () {
            // store data to products table
            $this->service->storeProduct();
            // store product rak
            $this->service->storeOrUpdateOpeningStock();
            // store product rak
            $this->service->storeOrUpdateProductRak();
            // store barcode
            $this->service->storeOrUpdateBarcode();
            // upload product image
            $this->service->uploadImage();
            // store product serials
            $this->service->storeProductSerials($this);

        });
    }

    public function updateProduct($product)
    {


        DB::transaction(function () use ($product) {

           $this->service->updateProduct($product);

            // store opening stock
            $this->service->storeOrUpdateOpeningStock();

            // store product rak
            $this->service->storeOrUpdateProductRak();

            // update barcode
            $this->service->storeOrUpdateBarcode();

            // upload product image
            //$this->service->uploadImage();
            $this->service->updateImage($product);

            // update product serials
            if($product->has_serial == 1){
                $this->service->updateProductSerials($this);
            }
        });
    }
}
