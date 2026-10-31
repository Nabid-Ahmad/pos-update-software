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
        Schema::table('product_uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('fk_company_id')->nullable();
        });
        Schema::table('customer_uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('fk_company_id')->nullable();
        });
        Schema::table('supplier_uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('fk_company_id')->nullable();
        });
        Schema::table('purchase_uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('fk_company_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_uploads', function (Blueprint $table) {
            $table->dropColumn('fk_company_id');
        });
        Schema::table('customer_uploads', function (Blueprint $table) {
            $table->dropColumn('fk_company_id');
        });
        Schema::table('supplier_uploads', function (Blueprint $table) {
            $table->dropColumn('fk_company_id');
        });
        Schema::table('purchase_uploads', function (Blueprint $table) {
            $table->dropColumn('fk_company_id');
        });
    }
};
