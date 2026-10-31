<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Category;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::firstOrCreate(
            ['category_name' => 'Mobile', 'fk_company_id' => 1],
            ['fk_created_by' => 1, 'fk_updated_by' => 1]
        );
    }
}
