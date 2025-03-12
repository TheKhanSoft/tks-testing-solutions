<?php

namespace Database\Seeders;

use App\Models\TestAttempt;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TestAttemptSeeder extends Seeder
{
    public function run(): void
    {
        $testAttempts = [
            // Usman's test attempts
            [
                'candiate_id' => 1, // Usman Ali
                'paper_id' => 1, // Computer Science Entry Test
                'start_time' => Carbon::now()->subDays(5)->setHour(10)->setMinute(0),
                'end_time' => Carbon::now()->subDays(5)->setHour(12)->setMinute(0),
                'score' => 78,
                'status' => 'completed',
            ],
            [
                'candiate_id' => 1, // Usman Ali 
                'paper_id' => 2, // GAT General
                'start_time' => Carbon::now()->subDays(2)->setHour(9)->setMinute(0),
                'end_time' => Carbon::now()->subDays(2)->setHour(12)->setMinute(0),
                'score' => 65,
                'status' => 'completed',
            ],
            
            // Aisha's test attempts
            [
                'candiate_id' => 2, // Aisha Bibi
                'paper_id' => 1, // Computer Science Entry Test
                'start_time' => Carbon::now()->subDays(4)->setHour(14)->setMinute(0),
                'end_time' => Carbon::now()->subDays(4)->setHour(16)->setMinute(0),
                'score' => 82,
                'status' => 'completed',
            ],
            
            // Mohammad Bilal's test attempts
            [
                'candiate_id' => 3, // Mohammad Bilal
                'paper_id' => 3, // Programming Midterm
                'start_time' => Carbon::now()->subDays(3)->setHour(11)->setMinute(0),
                'end_time' => Carbon::now()->subDays(3)->setHour(12)->setMinute(30),
                'score' => 42,
                'status' => 'completed',
            ],
            
            // Fatima's test attempts
            [
                'candiate_id' => 4, // Fatima Zahra
                'paper_id' => 2, // GAT General
                'start_time' => Carbon::now()->subDay(1)->setHour(9)->setMinute(0),
                'end_time' => Carbon::now()->subDay(1)->setHour(12)->setMinute(0),
                'score' => 88,
                'status' => 'completed',
            ],
            
            // Hamza's test attempts
            [
                'candiate_id' => 5, // Hamza Khan
                'paper_id' => 1, // Computer Science Entry Test
                'start_time' => Carbon::now()->subHours(5),
                'end_time' => Carbon::now()->subHours(3),
                'score' => 72,
                'status' => 'completed',
            ],
            
            // Sadia's test attempts - Still in progress
            [
                'candiate_id' => 6, // Sadia Malik
                'paper_id' => 3, // Programming Midterm
                'start_time' => Carbon::now()->subMinutes(30),
                'end_time' => null,
                'score' => null,
                'status' => 'in_progress',
            ],
        ];

        foreach ($testAttempts as $attempt) {
            TestAttempt::create($attempt);
        }
    }
}
