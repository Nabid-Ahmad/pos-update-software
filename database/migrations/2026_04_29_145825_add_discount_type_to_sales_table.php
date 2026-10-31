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
            $table->string('discount_type')->nullable()->after('sub_total');
            $table->string('special_discount_type')->nullable()->after('invoice_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('discount_type');
            $table->dropColumn('special_discount_type');
        });
    }
};
