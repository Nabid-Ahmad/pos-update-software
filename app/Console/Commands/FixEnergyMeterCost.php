<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixEnergyMeterCost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-energy-meter-cost';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle()
    {
        $product = \App\Product::where('product_code', '1290341005')->first();
        if ($product) {
            $this->info("Product ID: " . $product->id);
            $this->info("Current Product Cost: " . $product->product_cost);
            
            $updated = \App\SalesDetails::where('fk_product_id', $product->id)
                ->where('product_cost', '>', 5000)
                ->update(['product_cost' => 1129.15]);
                
            $this->info("Updated {$updated} old sales_details records where product_cost > 5000.");
        } else {
            $this->error("Product not found.");
        }
    }
}
