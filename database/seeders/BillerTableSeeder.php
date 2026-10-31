<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Biller;

class BillerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Biller::firstOrCreate(
            ['biller_code' => 'biller-1', 'fk_company_id' => 1],
            [
                'fk_created_by' => 1,
                'fk_updated_by' => 1,
                'name' => 'biller',
                'email' => 'biller@gmail.com',
                'phone' => '012646449',
                'address' => 'Dhaka'
            ]
        );
    }
}
