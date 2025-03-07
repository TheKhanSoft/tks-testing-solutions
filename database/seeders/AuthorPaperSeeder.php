<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;
use App\Models\Paper;

class AuthorPaperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = Author::all();
        $papers = Paper::all();

        // Assign authors to papers
        foreach ($papers as $paper) {
            $paper->authors()->attach($authors->random(rand(1, 2))->pluck('id')->toArray());
        }
    }
}
