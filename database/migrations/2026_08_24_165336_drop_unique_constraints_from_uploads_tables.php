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
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE product_uploads DROP INDEX product_uploads_product_name_unique'); } catch(\Exception $e){}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE purchase_uploads DROP INDEX purchase_uploads_product_name_unique'); } catch(\Exception $e){}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
