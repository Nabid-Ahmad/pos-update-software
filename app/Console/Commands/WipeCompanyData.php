<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WipeCompanyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:wipe {company_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipe transactional data for a specific company without deleting users/company settings.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->argument('company_id');

        $this->info("Starting data wipe for company ID: $companyId...");

        // Disable foreign key checks to prevent FK constraint errors during deletion
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Delete child tables (details) FIRST using INNER JOIN with their parent tables
        // This guarantees we only delete the data for THIS company, without needing fk_company_id in the details tables.
        $childQueries = [
            "DELETE sd FROM sales_details sd INNER JOIN sales s ON s.id = sd.sale_id WHERE s.fk_company_id = ?",
            "DELETE pd FROM purchase_details pd INNER JOIN purchases p ON p.id = pd.purchase_id WHERE p.fk_company_id = ?",
            "DELETE srd FROM sale_return_details srd INNER JOIN sale_returns sr ON sr.id = srd.sale_return_id WHERE sr.fk_company_id = ?",
            "DELETE prd FROM purchase_return_receives prd INNER JOIN purchase_returns pr ON pr.id = prd.purchase_return_id WHERE pr.fk_company_id = ?",
            "DELETE qd FROM quotation_details qd INNER JOIN quotations q ON q.id = qd.quotation_id WHERE q.fk_company_id = ?",
            "DELETE ar FROM account_reviews ar INNER JOIN users u ON u.id = ar.fk_created_by WHERE u.fk_company_id = ?",
            "DELETE cs FROM customer_suppliers cs INNER JOIN customers c ON c.id = cs.customer_id WHERE c.fk_company_id = ?"
        ];

        foreach ($childQueries as $query) {
            try {
                $deleted = DB::delete($query, [$companyId]);
                if ($deleted > 0) $this->info("Wiped $deleted child records.");
            } catch (\Exception $e) {
                // Ignore if table doesn't exist
            }
        }

        // Special handling for stock_transfers
        try {
            DB::table('stock_transfers')->where('from_company_id', $companyId)->orWhere('to_company_id', $companyId)->delete();
        } catch(\Exception $e) {}

        // 2. Now delete the main tables that directly have fk_company_id or company_id
        $tables = [
            // 1. Transaction & Ledger child tables
            'sales', 'purchases', 'sale_returns', 'purchase_returns', 'quotations', 
            'transactions', 'payments', 'due_collections', 'voucher_payments', 'vouchers', 
            'expenses', 'incomes', 'fund_transfers', 'liabilities', 'liability_purchases', 
            'account_balances', 
            
            // 2. Product-related child tables
            'product_stocks', 'product_serials', 'product_damages', 'product_packages', 
            'product_raks', 'random_returns', 'sale_return_exchanges',
            
            // 3. Other logs & standalone child tables
            'supplier_uploads', 'customer_uploads', 'product_uploads', 'purchase_uploads',
            'sms', 'sms_customers', 'sms_groups', 'sms_purchases', 'sms_suppliers',
            'assets', 'asset_purchases',
            
            // 4. Main Entities (Parents of transactions, children of categories)
            'products', 'customers', 'suppliers', 'marketers', 'billers', 'curriers', 'parties',
            
            // 5. Master Settings (Parents of everything)
            'categories', 'sub_categories', 'brands', 'product_units', 'customer_categories',
            'tax_rates', 'discounts', 'generics', 'groups', 'group_contacts', 'product_groups',
            'raks', 'warehouses'
        ];

        foreach ($tables as $table) {
            try {
                if (\Schema::hasColumn($table, 'fk_company_id')) {
                    $deleted = DB::table($table)->where('fk_company_id', $companyId)->delete();
                    if($deleted > 0) $this->info("Wiped $deleted records from $table.");
                } elseif (\Schema::hasColumn($table, 'company_id')) {
                    $deleted = DB::table($table)->where('company_id', $companyId)->delete();
                    if($deleted > 0) $this->info("Wiped $deleted records from $table.");
                }
            } catch (\Exception $e) {
                $this->warn("Skipped $table: " . $e->getMessage());
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info("Successfully wiped all transactional data for company $companyId!");
    }
}
