<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Party;

class PartyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Party::firstOrCreate(
            ['party_code' => 'party-1'],
            [
                'email' => 'party@gmail.com',
                'fk_company_id' => 1,
                'fk_created_by' => 1,
                'fk_updated_by' => 1,
                'name' => 'party',
                'phone' => '01711223344',
                'address' => 'dhaka',
            ]
        );        
    }
}
