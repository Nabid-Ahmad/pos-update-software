<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Payment;

class PaymentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $account = \App\Account::first();
        if ($account) {
            Payment::firstOrCreate(
                ['method_name' => 'bank', 'fk_company_id' => 1],
                [
                    'fk_created_by' => 1,
                    'fk_updated_by' => 1,
                    'fk_account' => $account->id,
                    'description' => 'bank payment',
                    'status' => '1',
                ]
            );   
        }
    }
}
