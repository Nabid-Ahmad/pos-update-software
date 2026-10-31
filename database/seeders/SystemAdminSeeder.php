<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\UserRole;

class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserRole::firstOrCreate(
            ['user_id' => 1, 'role_id' => 1],
            ['fk_company_id' => 1, 'fk_created_by' => 1, 'fk_updated_by' => 1]
        );
    }
}
