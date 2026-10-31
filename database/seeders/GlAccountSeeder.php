<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GlAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            'Assets',
            'Liabilities',
            'Income',
            'Costs'
        ];

        foreach ($accounts as $account) {
            \App\GlAccount::firstOrCreate(
                ['name' => $account],
                [
                    'created_by' => 1,
                    'updated_by' => 1,
                    'fk_company_id' => 1
                ]
            );
        }
    }
}
