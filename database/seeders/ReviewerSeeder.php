<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reviewer;

class ReviewerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Reviewer::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'expertise' => 'Quantum Computing',
        ]);

        Reviewer::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'expertise' => 'Artificial Intelligence',
        ]);

        Reviewer::create([
            'name' => 'Emily Johnson',
            'email' => 'emily.johnson@example.com',
            'expertise' => 'Blockchain Technology',
        ]);

        Reviewer::create([
            'name' => 'Michael Brown',
            'email' => 'michael.brown@example.com',
            'expertise' => 'Climate Science',
        ]);

        Reviewer::create([
            'name' => 'Sarah Davis',
            'email' => 'sarah.davis@example.com',
            'expertise' => 'Ethics in Technology',
        ]);
    }
}
