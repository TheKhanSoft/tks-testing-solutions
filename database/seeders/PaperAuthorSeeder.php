<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paper;
use App\Models\Author;

class PaperAuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $papers = Paper::all();
        $authors = Author::all();

        // Assign authors to papers
        foreach ($papers as $paper) {
            $paper->authors()->attach($authors->random(rand(1, 2))->pluck('id')->toArray());
        }
    }
}
