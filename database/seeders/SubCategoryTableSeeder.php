<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\SubCategory;

class SubCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SubCategory::firstOrCreate(
            ['sub_category_name' => 'smartphone', 'fk_category_id' => 1, 'fk_company_id' => 1],
            ['fk_created_by' => 1, 'fk_updated_by' => 1, 'sub_category_code' => 'smrt']
        );
    }
}
