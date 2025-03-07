<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paper;
use App\Models\Category;

class PaperCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $papers = Paper::all();
        $categories = Category::all();

        // Assign categories to papers
        foreach ($papers as $paper) {
            $paper->categories()->attach($categories->random(rand(1, 2))->pluck('id')->toArray());
        }
    }
}
