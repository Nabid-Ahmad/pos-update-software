<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $indexesToDrop = [
            'categories' => ['categories_category_code_unique', 'categories_category_name_unique', 'category_name', 'category_code'],
            'brands' => ['brands_name_unique', 'brands_code_unique', 'name', 'code'],
            'product_units' => ['product_units_name_unique', 'name'],
            'suppliers' => ['suppliers_supplier_code_unique', 'suppliers_email_unique', 'suppliers_phone_unique', 'supplier_code', 'email', 'phone'],
        ];

        foreach ($indexesToDrop as $table => $indexes) {
            foreach ($indexes as $index) {
                try {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
                } catch (\Exception $e) {
                    // Ignore if index doesn't exist
                }
            }
        }
    }

    public function down(): void
    {
        // Not easily reversible
    }
};
