<?php
namespace Database\Seeders;

use App\Payment;
use App\User;
use App\Company;
use Illuminate\Database\Seeder;

class PaymentMethodTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $user_id = User::first()->id ?? 1;
            $companies = Company::all();

            $methodNames = ['Cash', 'Check', 'Mobile Banking'];

            foreach ($companies as $company) {
                $data = [
                    'status'        => 1,
                    'fk_created_by' => $user_id,
                    'fk_updated_by' => $user_id,
                    'fk_company_id' => $company->id
                ];

                foreach ($methodNames as $name) {
                    Payment::firstOrCreate([
                        'method_name' => $name, 
                        'fk_company_id' => $company->id
                    ], $data);
                }
            }
        } catch (\Exception $e) {
            // Silently catch the error to prevent seeder from breaking in live environment
        }
    }
}
