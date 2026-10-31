<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Supplier;

class SupplierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Supplier::firstOrCreate(
            ['supplier_code' => 'supplier-1'],
            [
                'fk_company_id' => 1,
                'email' => 'supplier@gmail.com',
                'fk_created_by' => 1,
                'fk_updated_by' => 1,
                'name' => 'supplier',
                'phone' => '01711223344',
                'address' => 'Dhaka'
            ]
        );
    }
}
