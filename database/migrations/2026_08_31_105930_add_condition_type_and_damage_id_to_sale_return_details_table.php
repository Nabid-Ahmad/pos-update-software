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
        Schema::table('sale_return_details', function (Blueprint $table) {
            $table->enum('condition_type', ['Good', 'Damage'])->default('Good')->after('subtotal');
            $table->unsignedBigInteger('product_damage_id')->nullable()->after('condition_type');
        });

        // Migrate old data
        $details = \App\SaleReturnDetail::whereNull('product_damage_id')->get();
        foreach ($details as $detail) {
            if ($detail->created_at) {
                $damageItem = \App\Models\Inventory\ProductDamageItem::where('fk_product_id', $detail->product_id)
                    ->where('quantity', $detail->quantity)
                    ->whereBetween('created_at', [
                        \Carbon\Carbon::parse($detail->created_at)->subSeconds(10), 
                        \Carbon\Carbon::parse($detail->created_at)->addSeconds(10)
                    ])
                    ->first();
                    
                if ($damageItem) {
                    $detail->update([
                        'condition_type' => 'Damage',
                        'product_damage_id' => $damageItem->product_damage_id
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_return_details', function (Blueprint $table) {
            $table->dropColumn(['condition_type', 'product_damage_id']);
        });
    }
};
