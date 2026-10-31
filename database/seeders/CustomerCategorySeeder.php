<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomerCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
{
    \App\CustomerCategory::firstOrCreate(
        ['name' => 'New Customer', 'company_id' => 1],
        [
            'type' => 'percent',
            'created_by' => 1,
            'updated_by' => 1,
            'description' => 'This is for new customer',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]
    );
}
}
