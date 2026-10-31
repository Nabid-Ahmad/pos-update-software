<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $indexesToDrop = [
            'customers_customer_code_unique',
            'customers_phone_unique',
            'customers_email_unique'
        ];

        foreach ($indexesToDrop as $index) {
            try {
                DB::statement("ALTER TABLE `customers` DROP INDEX `{$index}`");
            } catch (\Exception $e) {
                // Ignore if index doesn't exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not easily reversible
    }
};
