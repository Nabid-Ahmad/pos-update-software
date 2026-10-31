<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_details', function (Blueprint $table) {
            // Foreign key drop করো
            $table->dropForeign(['warehouse_id']);
        });
        
        // এখন warehouse_id nullable করো
        Schema::table('sales_details', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sales_details', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable(false)->change();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
        });
    }
};