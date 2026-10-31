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
        Schema::create('customer_suppliers', function (Blueprint $table) {
            
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('supplier_id')->constrained();

            $table->decimal('due_limit', 10, 2)->nullable();
            $table->decimal('balance', 10, 2)->nullable();

            $table->primary(['customer_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_suppliers');
    }
};
