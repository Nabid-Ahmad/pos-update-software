<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\AccountChart;

class AccountChartTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AccountChart::firstOrCreate(
            ['head_name' => 'Electricity Bill', 'fk_company_id' => 1],
            [
                'fk_created_by' => 1,
                'fk_updated_by' => 1,
                'head_type' => '1',
                'status' => '1',
            ]
        );
    }
}
