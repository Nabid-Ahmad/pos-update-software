<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\ProductUnit;

class ProductUnitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProductUnit::firstOrCreate(
            ['name' => 'piece', 'fk_company_id' => 1],
            ['fk_created_by' => 1, 'fk_updated_by' => 1, 'description' => 'Quantity counted as piece', 'status' => 1]
        );
    }
}
