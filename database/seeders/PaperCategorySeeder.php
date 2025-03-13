<?php

namespace Database\Seeders;

use App\Models\PaperCategory;
use Illuminate\Database\Seeder;

class PaperCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Programming',
                'description' => 'Tests related to programming and software development',
            ],
            [
                'name' => 'Mathematics',
                'description' => 'Tests covering various mathematics topics',
            ],
            [
                'name' => 'Science',
                'description' => 'Tests covering physics, chemistry and biology',
            ],
            [
                'name' => 'General Knowledge',
                'description' => 'Tests covering general knowledge topics',
            ],
        ];

        foreach ($categories as $category) {
            PaperCategory::create($category);
        }
    }
}
