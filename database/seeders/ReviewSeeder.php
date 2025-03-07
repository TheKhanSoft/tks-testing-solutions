<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Review::create([
            'paper_id' => 1,
            'reviewer_name' => 'John Doe',
            'rating' => 5,
            'comment' => 'Excellent paper on quantum computing!',
        ]);

        Review::create([
            'paper_id' => 2,
            'reviewer_name' => 'Jane Smith',
            'rating' => 4,
            'comment' => 'Great insights on AI advancements.',
        ]);

        Review::create([
            'paper_id' => 3,
            'reviewer_name' => 'Emily Johnson',
            'rating' => 5,
            'comment' => 'Very informative on blockchain applications in healthcare.',
        ]);

        Review::create([
            'paper_id' => 4,
            'reviewer_name' => 'Michael Brown',
            'rating' => 4,
            'comment' => 'Interesting approaches to climate modeling using machine learning.',
        ]);

        Review::create([
            'paper_id' => 5,
            'reviewer_name' => 'Sarah Davis',
            'rating' => 5,
            'comment' => 'Thought-provoking discussion on the ethics of autonomous vehicles.',
        ]);
    }
}
