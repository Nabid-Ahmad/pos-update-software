<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        try {
            Schema::table('sales_details', function (Blueprint $table) {
                // Foreign key drop করো
                $table->dropForeign(['warehouse_id']);
            });
        } catch (\Exception $e) {
            // Foreign key না থাকলে silent fail করবে
        }
    }

    public function down()
    {
        try {
            Schema::table('sales_details', function (Blueprint $table) {
                // Rollback এ foreign key আবার add করো
                $table->foreign('warehouse_id')->references('id')->on('warehouses');
            });
        } catch (\Exception $e) {
            // কোনো এরর হলে স্কিপ করবে
        }
    }
};