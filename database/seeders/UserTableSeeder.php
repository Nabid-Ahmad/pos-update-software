<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Str;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::firstOrCreate(['email' => 'admin@gmail.com'], [
            'name' => 'Super Admin',
            'email_verified_at' => now(),
            'password' => bcrypt('admin1234'),
            'remember_token' => \Illuminate\Support\Str::random(10),
            'fk_company_id' => '1',
            'phone' => '01303365662'
        ]);

        \DB::table('user_roles')->updateOrInsert(
            ['user_id' => $admin->id],
            [
                'role_id' => 1,
                'fk_company_id' => 1,
                'fk_created_by' => 1,
                'fk_updated_by' => 1
            ]
        );
    }
}
