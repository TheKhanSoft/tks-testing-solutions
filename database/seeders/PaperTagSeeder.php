<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paper;
use App\Models\Tag;

class PaperTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $papers = Paper::all();
        $tags = Tag::all();

        // Assign tags to papers
        foreach ($papers as $paper) {
            $paper->tags()->attach($tags->random(rand(1, 3))->pluck('id')->toArray());
        }
    }
}
