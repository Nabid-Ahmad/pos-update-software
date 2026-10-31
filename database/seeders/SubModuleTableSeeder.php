<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\SubModule;

class SubModuleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SubModule::firstOrCreate(
            ['name' => 'Company Information', 'fk_module_id' => '1'],
            ['fk_company_id' => '1']
        );
    }
}
