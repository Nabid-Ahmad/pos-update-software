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
        Schema::table('software_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('software_payments', 'duration_in_months')) {
                $table->integer('duration_in_months')->default(1)->after('grace_period');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('software_payments', function (Blueprint $table) {
            if (Schema::hasColumn('software_payments', 'duration_in_months')) {
                $table->dropColumn('duration_in_months');
            }
        });
    }
};
