<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paper;
use App\Models\Publisher;

class PaperPublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $papers = Paper::all();
        $publishers = Publisher::all();

        // Assign publishers to papers
        foreach ($papers as $paper) {
            $paper->publishers()->attach($publishers->random(1)->pluck('id')->toArray());
        }
    }
}
