<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        try {
            Schema::table('products', function (Blueprint $table) {
                $sm = Schema::getConnection()->getSchemaBuilder();
    
                if ($sm->hasIndex('products', 'products_company_product_code_unique')) {
                    $table->dropUnique('products_company_product_code_unique');
                }
                if ($sm->hasIndex('products', 'products_company_product_name_unique')) {
                    $table->dropUnique('products_company_product_name_unique');
                }
            });
        } catch (\Exception $e) {
            // Ignore error if index does not exist or foreign key constraint fails
        }

        try {
            Schema::table('products', function (Blueprint $table) {
                $sm = Schema::getConnection()->getSchemaBuilder();
                // Re-add as regular index for performance
                if (!$sm->hasIndex('products', 'products_company_product_code_index')) {
                    $table->index(['fk_company_id', 'product_code'], 'products_company_product_code_index');
                }
                if (!$sm->hasIndex('products', 'products_company_product_name_index')) {
                    $table->index(['fk_company_id', 'product_name'], 'products_company_product_name_index');
                }
            });
        } catch (\Exception $e) {
            // Ignore error
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            Schema::table('products', function (Blueprint $table) {
                $sm = Schema::getConnection()->getSchemaBuilder();
    
                if ($sm->hasIndex('products', 'products_company_product_code_index')) {
                    $table->dropIndex('products_company_product_code_index');
                }
                if ($sm->hasIndex('products', 'products_company_product_name_index')) {
                    $table->dropIndex('products_company_product_name_index');
                }
            });
        } catch (\Exception $e) {
            // Ignore error
        }

        try {
            Schema::table('products', function (Blueprint $table) {
                $sm = Schema::getConnection()->getSchemaBuilder();
                
                if (!$sm->hasIndex('products', 'products_company_product_code_unique')) {
                    $table->unique(['fk_company_id', 'product_code'], 'products_company_product_code_unique');
                }
                if (!$sm->hasIndex('products', 'products_company_product_name_unique')) {
                    $table->unique(['fk_company_id', 'product_name'], 'products_company_product_name_unique');
                }
            });
        } catch (\Exception $e) {
            // Ignore error
        }
    }
};
