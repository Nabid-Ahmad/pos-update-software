<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Product;
use App\Category;
use App\Brand;
use App\Generic;
use App\Rak;
use App\ProductUnit;
use App\Supplier;
use App\ProductRak;
use Illuminate\Support\Facades\DB;

class FixProductUploadTenancy extends Command
{
    protected $signature = 'fix:product-upload-tenancy';
    protected $description = 'Fixes product relations that got assigned IDs from other companies due to global CSV upload query bug';

    public function handle()
    {
        $this->info('Starting tenancy fix for products...');

        $products = Product::withTrashed()->get();
        $fixedCount = 0;

        foreach ($products as $product) {
            $companyId = $product->fk_company_id;
            $updated = false;
            
            if (!$companyId) continue;

            // 1. Supplier
            if ($product->supplier_id) {
                $supplier = Supplier::withTrashed()->find($product->supplier_id);
                if ($supplier && $supplier->fk_company_id != $companyId) {
                    // Try to find matching supplier in correct company
                    $correctSupplier = Supplier::withTrashed()
                        ->where('fk_company_id', $companyId)
                        ->where('name', $supplier->name)
                        ->first();
                    
                    if (!$correctSupplier) {
                        $maxId = Supplier::withTrashed()->max('id') + 1;
                        $correctSupplier = Supplier::create([
                            'name' => $supplier->name,
                            'fk_company_id' => $companyId,
                            'fk_created_by' => $product->fk_created_by,
                            'fk_updated_by' => $product->fk_updated_by,
                            'supplier_code' => 'supplier-' . $maxId
                        ]);
                    }
                    $product->supplier_id = $correctSupplier->id;
                    $updated = true;
                    $this->info("Fixed Supplier for Product ID: {$product->id}");
                }
            }

            // 2. Category
            if ($product->fk_category_id) {
                $category = Category::withTrashed()->find($product->fk_category_id);
                if ($category && $category->fk_company_id != $companyId) {
                    $correctCategory = Category::withTrashed()
                        ->where('fk_company_id', $companyId)
                        ->where('category_name', $category->category_name)
                        ->first();
                    
                    if (!$correctCategory) {
                        $maxCatId = Category::withTrashed()->max('id') + 1;
                        $correctCategory = Category::create([
                            'category_name' => $category->category_name,
                            'category_code' => 'cat-' . $maxCatId,
                            'fk_company_id' => $companyId,
                            'fk_created_by' => $product->fk_created_by,
                            'fk_updated_by' => $product->fk_updated_by,
                        ]);
                    }
                    $product->fk_category_id = $correctCategory->id;
                    $updated = true;
                    $this->info("Fixed Category for Product ID: {$product->id}");
                }
            }

            // 3. Brand
            if ($product->brand_id) {
                $brand = Brand::withTrashed()->find($product->brand_id);
                if ($brand && $brand->company_id != $companyId) {
                    $correctBrand = Brand::withTrashed()
                        ->where('company_id', $companyId)
                        ->where('name', $brand->name)
                        ->first();
                    
                    if (!$correctBrand) {
                        $correctBrand = Brand::create([
                            'name' => $brand->name,
                            'code' => '9999',
                            'company_id' => $companyId,
                            'created_by' => $product->fk_created_by,
                            'updated_by' => $product->fk_updated_by,
                        ]);
                        $correctBrand->update(['code' => ($correctBrand->id + 1001)]);
                    }
                    $product->brand_id = $correctBrand->id;
                    $updated = true;
                    $this->info("Fixed Brand for Product ID: {$product->id}");
                }
            }

            // 4. Generic
            if ($product->generic_id) {
                $generic = Generic::find($product->generic_id);
                if ($generic && $generic->fk_company_id != $companyId) {
                    $correctGeneric = Generic::where('fk_company_id', $companyId)
                        ->where('name', $generic->name)
                        ->first();
                    
                    if (!$correctGeneric) {
                        $correctGeneric = Generic::create([
                            'name' => $generic->name,
                            'status' => 1,
                            'fk_company_id' => $companyId,
                            'fk_created_by' => $product->fk_created_by,
                            'fk_updated_by' => $product->fk_updated_by,
                        ]);
                    }
                    $product->generic_id = $correctGeneric->id;
                    $updated = true;
                    $this->info("Fixed Generic for Product ID: {$product->id}");
                }
            }

            // 5. ProductUnit
            if ($product->fk_product_unit_id) {
                $unit = ProductUnit::find($product->fk_product_unit_id);
                if ($unit && $unit->fk_company_id != $companyId) {
                    $correctUnit = ProductUnit::where('fk_company_id', $companyId)
                        ->where('name', $unit->name)
                        ->first();
                    
                    if (!$correctUnit) {
                        $correctUnit = ProductUnit::create([
                            'name' => $unit->name,
                            'status' => 1,
                            'fk_company_id' => $companyId,
                            'fk_created_by' => $product->fk_created_by,
                            'fk_updated_by' => $product->fk_updated_by,
                        ]);
                    }
                    $product->fk_product_unit_id = $correctUnit->id;
                    $updated = true;
                    $this->info("Fixed Unit for Product ID: {$product->id}");
                }
            }
            
            if ($updated) {
                $product->save();
                $fixedCount++;
            }
            
            // 6. ProductRak
            $productRaks = ProductRak::where('product_id', $product->id)->get();
            foreach ($productRaks as $pRak) {
                if ($pRak->fk_company_id != $companyId) {
                    $pRak->fk_company_id = $companyId;
                    $pRak->save();
                    $this->info("Fixed ProductRak Company ID for Product ID: {$product->id}");
                }
                
                $rak = Rak::find($pRak->rak_id);
                if ($rak && $rak->fk_company_id != $companyId) {
                    $correctRak = Rak::where('fk_company_id', $companyId)
                        ->where('name', $rak->name)
                        ->first();
                    
                    if (!$correctRak) {
                        $correctRak = Rak::create([
                            'name' => $rak->name,
                            'status' => 1,
                            'fk_company_id' => $companyId,
                            'fk_created_by' => $product->fk_created_by,
                            'fk_updated_by' => $product->fk_updated_by,
                        ]);
                    }
                    $pRak->rak_id = $correctRak->id;
                    $pRak->save();
                    $this->info("Fixed Rak for Product ID: {$product->id}");
                }
            }
        }

                $this->info("Completed. Fixed {$fixedCount} products.");

        $this->info("Starting cleanup of duplicate suppliers...");
        $companies = \App\Company::all();
        foreach ($companies as $company) {
            $suppliers = Supplier::where('fk_company_id', $company->id)->get();
            $grouped = $suppliers->groupBy('name');
            foreach ($grouped as $name => $duplicates) {
                if ($duplicates->count() > 1) {
                    $first = $duplicates->first();
                    $others = $duplicates->slice(1);
                    foreach ($others as $other) {
                        // Move products to first
                        Product::where('supplier_id', $other->id)->update(['supplier_id' => $first->id]);
                        // Delete duplicate
                        $other->forceDelete();
                    }
                    $this->info("Merged " . $others->count() . " duplicates for supplier '{$name}' in company {$company->id}");
                }
            }
        }
        $this->info("Cleanup completed.");
    }
}
