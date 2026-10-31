<?php


namespace App\Services\Products;


use App\Barcode;
use App\Brand;
use App\Category;
use App\Company;
use App\Generic;
use App\Product;
use App\ProductRak;
use App\ProductStock;
use App\ProductUnit;
use App\Rak;
use App\Supplier;

class ProductUploadService
{
    public function getUploadedProductCategory($category_name)
    {
        $category_name = trim(ucwords($category_name));
        $category = Category::withTrashed()->where(function($q) use ($category_name) {
            $q->where('category_name', $category_name)->orWhere('id', $category_name);
        })->where('fk_company_id', companyId())->first();

        if ($category && $category->trashed()) {
            $category->restore();
        }

        return $category_id = optional($category)->id ?? Category::create([
                'fk_company_id' => companyId(),
                'fk_created_by' => auth()->id(),
                'fk_updated_by' => auth()->id(),
                'category_name' => ucwords($category_name)
            ])->id;
    }


    public function getUploadedProductBrand($brand_name) {
        $brand_name = trim(ucwords($brand_name));
        $brand = Brand::withTrashed()->where(function($q) use ($brand_name) {
            $q->where('name', $brand_name)->orWhere('id', $brand_name);
        })->where('company_id', companyId())->first();
        if($brand) {
            if ($brand->trashed()) {
                $brand->restore();
            }
            return $brand->id;
        }


        $brand = Brand::create([
            'name' => ucwords($brand_name),
            'code' => '9999',
            'company_id' => companyId(),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
        $brand->update(['code' => ($brand->id + 1001)]);
        return $brand->id;
    }

    public function getUploadedProductGeneric($generic_name)
    {
        $generic_name = trim(ucwords($generic_name));
        $generic = Generic::where(function($q) use ($generic_name) {
            $q->where('name', $generic_name)->orWhere('id', $generic_name);
        })->where('fk_company_id', companyId() ?? Company::first()->id)->first();
        return optional($generic)->id ??  Generic::create([
                'name' => ucwords($generic_name),
                'status' => 1,
                'fk_company_id' => companyId() ?? Company::first()->id,
                'fk_created_by' => auth()->id(),
                'fk_updated_by' => auth()->id(),
            ])->id;
    }


    public function getUploadedProductRakId($product_rak_no, $product)
    {
        $rak_id = $this->getRakId($product_rak_no);

        return ProductRak::create([
            'fk_company_id' => companyId() ?? 1,
            'product_id' => $product->id,
            'rak_id' => $rak_id
        ])->id;
    }

    private function getRakId($product_rak_no)
    {
        $product_rak_no = trim(ucwords($product_rak_no));
        $product_rak = Rak::where(function($q) use ($product_rak_no) {
            $q->where('name', $product_rak_no)->orWhere('id', $product_rak_no);
        })->where('fk_company_id', companyId() ?? Company::first()->id)->first();

        return $rak_id = optional($product_rak)->id ?? Rak::create([
                'name' => ucwords($product_rak_no),
                'status' => 1,
                'fk_company_id' => companyId() ?? Company::first()->id,
                'fk_created_by' => auth()->id(),
                'fk_updated_by' => auth()->id(),
            ])->id;
    }

    public function getUploadedProductUnit($product_unit_name)
    {
        $product_unit_name = trim(ucwords($product_unit_name));
        $product_unit = ProductUnit::where(function($q) use ($product_unit_name) {
            $q->where('name', $product_unit_name)->orWhere('id', $product_unit_name);
        })->where('fk_company_id', companyId())->first();
        return optional($product_unit)->id ??  ProductUnit::create([
                'name' => ucwords($product_unit_name),
                'status' => 1,
                'fk_company_id' => companyId(),
                'fk_created_by' => auth()->id(),
                'fk_updated_by' => auth()->id(),
            ])->id;
    }



    public function getUploadedProductData($product)
    {
        return [
            'product_name'              => $product->product_name,
            'product_code'              => $product->product_code ?? 'product-' . (Product::withTrashed()->max('id') + 1),
            'product_description'       => $product->product_description,
            'product_cost'              => $product->product_cost ?? 0,
            'product_price'             => $product->product_price ?? 0,
            'product_alert_quantity'    => $product->product_alert_quantity ?? 0,
            'opening_quantity'          => $product->opening_quantity ?? 0,
            'tax'                       => $product->tax ?? 0,
            'expire_date'               => $product->expire_date,

            'fk_company_id' => companyId(),
            'fk_created_by' => auth()->id(),
            'fk_updated_by' => auth()->id()
        ];
    }

    public function storeProductStock($product, $opening_quantity)
    {
        $product_stock = ProductStock::where('fk_company_id', companyId())->where('fk_product_id', $product->id)->count();

        if ($opening_quantity != '') {
            $product->product_stock()->create([
                'opening_quantity'      => $opening_quantity,
                'purchased_quantity'    => 0,
                'available_quantity'    => $opening_quantity,
                'sold_quantity'         => 0
            ]);
        }
    }


    public function storeOrUpdateBarcode($product_id, $barcode_number)
    {
        Barcode::updateOrCreate(
            ['product_id' => $product_id],
            ['barcode_number' => $barcode_number]
        );
    }

    public function getUploadedProductSupplier($supplier_name)
    {
        if (!$supplier_name) return null;
        $supplier_name = trim(ucwords($supplier_name));
        $supplier = Supplier::withTrashed()->where(function($q) use ($supplier_name) {
            $q->where('name', $supplier_name)->orWhere('id', $supplier_name);
        })->where('fk_company_id', companyId())->first();
        if ($supplier) {
            if ($supplier->trashed()) {
                $supplier->restore();
            }
            return $supplier->id;
        }

        $supplier = Supplier::create([
            'name' => ucwords($supplier_name),
            'fk_company_id' => companyId(),
            'fk_created_by' => auth()->id(),
            'fk_updated_by' => auth()->id(),
        ]);
        return $supplier->id;
    }
}
