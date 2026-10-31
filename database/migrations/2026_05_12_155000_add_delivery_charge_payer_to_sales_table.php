<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryChargePayerToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'delivery_charge')) {
                $table->decimal('delivery_charge', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('sales', 'delivery_charge_payer')) {
                $table->string('delivery_charge_payer')->default('company');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'delivery_charge_payer')) {
                $table->dropColumn('delivery_charge_payer');
            }
        });
    }
}
