<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GlAccountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $accounts = ['Assets', 'Liabilities', 'Income', 'Costs'];
            $auth_id = \App\User::first()->id ?? 1;

            foreach ($accounts as $key => $account) {
                \App\GlAccount::firstOrCreate(
                    ['name' => $account],
                    [
                        'created_by' => $auth_id,
                        'updated_by' => $auth_id,
                    ]
                );
            }
        } catch (\Exception $e) {
            // Silently catch the error to prevent seeder from breaking
        }
    }
}
