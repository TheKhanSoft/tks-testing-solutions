<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tag::create([
            'name' => 'Quantum',
        ]);

        Tag::create([
            'name' => 'AI',
        ]);

        Tag::create([
            'name' => 'Blockchain',
        ]);

        Tag::create([
            'name' => 'Climate',
        ]);

        Tag::create([
            'name' => 'Ethics',
        ]);

        Tag::create([
            'name' => 'Cybersecurity',
        ]);

        Tag::create([
            'name' => 'Renewable',
        ]);

        Tag::create([
            'name' => 'Genomics',
        ]);

        Tag::create([
            'name' => 'Big Data',
        ]);

        Tag::create([
            'name' => 'Genetic Engineering',
        ]);
    }
}
