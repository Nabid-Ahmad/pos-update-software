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
        if (Schema::hasTable('marketers') && !Schema::hasColumn('marketers', 'fk_company_id')) {
            Schema::table('marketers', function (Blueprint $table) {
                $table->unsignedBigInteger('fk_company_id')->default(1)->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('marketers') && Schema::hasColumn('marketers', 'fk_company_id')) {
            Schema::table('marketers', function (Blueprint $table) {
                $table->dropColumn('fk_company_id');
            });
        }
    }
};
