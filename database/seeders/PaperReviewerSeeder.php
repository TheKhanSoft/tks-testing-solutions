<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paper;
use App\Models\Reviewer;

class PaperReviewerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $papers = Paper::all();
        $reviewers = Reviewer::all();

        // Assign reviewers to papers
        foreach ($papers as $paper) {
            $paper->reviewers()->attach($reviewers->random(rand(1, 2))->pluck('id')->toArray());
        }
    }
}
