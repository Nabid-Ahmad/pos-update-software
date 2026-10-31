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
        if (!Schema::hasColumn('product_stocks', 'free_quantity')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                $table->decimal('free_quantity', 10, 2)->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('product_stocks', 'free_quantity')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                $table->dropColumn('free_quantity');
            });
        }
    }
};
