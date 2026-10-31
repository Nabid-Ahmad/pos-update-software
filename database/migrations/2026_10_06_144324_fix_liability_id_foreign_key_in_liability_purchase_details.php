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
        Schema::table('liability_purchase_details', function (Blueprint $table) {
            $table->dropForeign('liability_purchase_details_liability_id_foreign');
            $table->foreign('liability_id')->references('id')->on('liabilities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liability_purchase_details', function (Blueprint $table) {
            $table->dropForeign(['liability_id']);
            $table->foreign('liability_id')->references('id')->on('users');
        });
    }
};
