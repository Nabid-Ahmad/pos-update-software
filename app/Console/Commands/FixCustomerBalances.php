<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Customer;
use App\Services\SalesService;
use App\Models\CustomerSupplier;

class FixCustomerBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate and fix all customer balances to remove ghost dues from deleted sales';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Starting Customer Balance Recalculation...");
        
        // Login the first admin user to bypass the auth()->user() null error in models
        $user = \App\User::first();
        if ($user) {
            \Auth::login($user);
        }

        $customers = Customer::all();
        $salesService = new SalesService();
        $fixedCount = 0;

        foreach($customers as $customer) {
            $oldBalance = $customer->current_balance;
            
            // If the customer has NO sales, wipe out their opening balance and set everything to 0
            if ($customer->sale()->count() == 0) {
                if ($customer->customer_previous_due != 0 || $customer->current_balance != 0) {
                    $this->info("WIPING OUT Balance for Customer: {$customer->name} (ID: {$customer->id}) | Had OB: {$customer->customer_previous_due}");
                    
                    $customer->customer_previous_due = 0;
                    $customer->current_balance = 0;
                    $customer->save();
                    
                    // Also wipe out any CustomerSupplier ghost records
                    \App\Models\CustomerSupplier::where('customer_id', $customer->id)->delete();
                    
                    $fixedCount++;
                }
            } else {
                // For customers WITH sales, just recalculate their balance
                $salesService->updateCustomerBalance($customer->id);
                
                // Fetch updated customer
                $customer->refresh();
                
                if (abs((float)$oldBalance - (float)$customer->current_balance) > 0.01) {
                    $this->info("FIXED Balance for Customer: {$customer->name} (ID: {$customer->id}) | Old: {$oldBalance} | New: {$customer->current_balance}");
                    $fixedCount++;
                }
            }
        }

        $this->info("--------------------------------------------------");
        $this->info("Done! Successfully fixed {$fixedCount} customers with ghost dues.");
    }
}
