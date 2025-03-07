<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conference;

class ConferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Conference::create([
            'name' => 'International Conference on Quantum Computing',
            'location' => 'San Francisco, CA',
            'date' => '2023-11-15',
        ]);

        Conference::create([
            'name' => 'AI and Machine Learning Summit',
            'location' => 'New York, NY',
            'date' => '2023-12-05',
        ]);

        Conference::create([
            'name' => 'Blockchain Expo',
            'location' => 'Las Vegas, NV',
            'date' => '2024-01-20',
        ]);

        Conference::create([
            'name' => 'Climate Science Symposium',
            'location' => 'Denver, CO',
            'date' => '2024-02-10',
        ]);

        Conference::create([
            'name' => 'Ethics in Technology Conference',
            'location' => 'Boston, MA',
            'date' => '2024-03-25',
        ]);
    }
}
