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
        Schema::table('account_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_return_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_reviews', function (Blueprint $table) {
            $table->dropColumn('sale_return_id');
        });
    }
};
