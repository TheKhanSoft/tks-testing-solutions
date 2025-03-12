<?php

namespace Database\Seeders;

use App\Models\PaperCategory;
use Illuminate\Database\Seeder;

class PaperCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'GAT General',
                'description' => 'Graduate Assessment Test for general subjects',
            ],
            [
                'name' => 'GAT Subject',
                'description' => 'Graduate Assessment Test for specific subjects',
            ],
            [
                'name' => 'Entry Test',
                'description' => 'University admission entry test',
            ],
            [
                'name' => 'Midterm Exam',
                'description' => 'Mid-semester examination',
            ],
            [
                'name' => 'Final Exam',
                'description' => 'End-of-semester examination',
            ],
        ];

        foreach ($categories as $category) {
            PaperCategory::create($category);
        }
    }
}
