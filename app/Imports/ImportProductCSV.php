<?php

namespace App\Imports;

use App\Product;
use App\ProductUpload;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportProductCSV implements ToModel, WithHeadingRow
{
    private static $seenNames = [];

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Skip empty rows or rows without a product name
        if (empty($row['product_name'])) {
            return null;
        }

        $name = trim($row['product_name']);

        // Check for duplicates in the current CSV
        if (in_array($name, self::$seenNames)) {
            return null;
        }
        self::$seenNames[] = $name;

        // Check if already pending in product_uploads
        if (ProductUpload::where('product_name', $name)->where('fk_company_id', companyId())->exists()) {
            return null;
        }

        return new ProductUpload([
            'product_name'              => $name,
            'product_code'              => $row['product_code'] ?? null,
            'fk_category_id'            => !empty($row['category_id']) ? $row['category_id'] : 'General',
            'brand_id'                  => $row['brand_id'] ?? null,
            'fk_product_unit_id'        => !empty($row['unit_id']) ? $row['unit_id'] : 'Piece',
            'product_description'       => $row['description'] ?? null,
            'product_cost'              => !empty($row['purchase_price']) ? str_replace(',', '', $row['purchase_price']) : 0,
            'product_price'             => !empty($row['sale_price']) ? str_replace(',', '', $row['sale_price']) : 0,
            'tax'                       => !empty($row['tax']) ? str_replace(',', '', $row['tax']) : 0,
            'opening_quantity'          => !empty($row['showroom_opening_quantity']) ? str_replace(',', '', $row['showroom_opening_quantity']) : 0,
            'product_alert_quantity'    => !empty($row['alert_quantity']) ? str_replace(',', '', $row['alert_quantity']) : 0,
            'expire_date'               => $row['expire_date'] ?? null,
            'barcode_id'                => $row['barcode'] ?? null,
            'generic_id'                => $row['generic'] ?? null,
            'product_rak_no'            => $row['product_rak_no'] ?? null,
            'supplier_name'             => $row['supplier_name'] ?? null,
            'warranty_days'             => $row['warranty'] ?? null,
            'fk_company_id'             => companyId(),
        ]);
    }
}
