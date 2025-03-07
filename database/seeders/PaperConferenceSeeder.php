<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paper;
use App\Models\Conference;

class PaperConferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $papers = Paper::all();
        $conferences = Conference::all();

        // Assign conferences to papers
        foreach ($papers as $paper) {
            $paper->conferences()->attach($conferences->random(1)->pluck('id')->toArray());
        }
    }
}
