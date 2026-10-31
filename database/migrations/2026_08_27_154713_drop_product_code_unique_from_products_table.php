<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $sm = Schema::getConnection()->getSchemaBuilder();
            if ($sm->hasIndex('products', 'products_product_code_unique')) {
                $table->dropUnique('products_product_code_unique');
            }
            if (!$sm->hasIndex('products', 'products_company_product_code_unique')) {
                $table->unique(['fk_company_id', 'product_code'], 'products_company_product_code_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_company_product_code_unique');
            $table->unique('product_code', 'products_product_code_unique');
        });
    }
};
