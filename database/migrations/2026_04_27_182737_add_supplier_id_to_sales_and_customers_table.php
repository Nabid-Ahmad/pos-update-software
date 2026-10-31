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
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->after('fk_customer_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->after('fk_updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('supplier_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('supplier_id');
        });
    }
};
