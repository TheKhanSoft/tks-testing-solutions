<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                'name' => 'Programming Fundamentals',
                'code' => 'PF',
                'description' => 'Basic programming concepts and principles',
            ],
            [
                'name' => 'Data Structures',
                'code' => 'DS',
                'description' => 'Data structures and algorithms',
            ],
            [
                'name' => 'Database Systems',
                'code' => 'DBS',
                'description' => 'Database design, SQL, and database management',
            ],
            [
                'name' => 'Calculus',
                'code' => 'CAL',
                'description' => 'Differential and integral calculus',
            ],
            [
                'name' => 'Physics',
                'code' => 'PHY',
                'description' => 'Basic physics concepts',
            ],
            [
                'name' => 'Chemistry',
                'code' => 'CHEM',
                'description' => 'Basic chemistry concepts',
            ],
            [
                'name' => 'Biology',
                'code' => 'BIO',
                'description' => 'Basic biology concepts',
            ],
            [
                'name' => 'English Composition',
                'code' => 'ENG',
                'description' => 'English writing and grammar',
            ],
            [
                'name' => 'Islamic History',
                'code' => 'ISH',
                'description' => 'History of Islam',
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
