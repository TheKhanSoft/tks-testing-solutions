<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Computer Science subjects
            [
                'name' => 'Programming Fundamentals',
                'code' => 'CS101',
                'department_id' => 1,
                'description' => 'Introduction to programming concepts and problem solving',
            ],
            [
                'name' => 'Data Structures',
                'code' => 'CS201',
                'department_id' => 1,
                'description' => 'Study of data structures and algorithms',
            ],
            [
                'name' => 'Database Systems',
                'code' => 'CS301',
                'department_id' => 1,
                'description' => 'Design and implementation of database systems',
            ],
            
            // Mathematics subjects
            [
                'name' => 'Calculus',
                'code' => 'MTH101',
                'department_id' => 2,
                'description' => 'Differential and integral calculus',
            ],
            [
                'name' => 'Linear Algebra',
                'code' => 'MTH201',
                'department_id' => 2,
                'description' => 'Study of vectors, matrices and linear transformations',
            ],
            
            // Physics subjects
            [
                'name' => 'Classical Mechanics',
                'code' => 'PHY101',
                'department_id' => 3,
                'description' => 'Newton\'s laws and classical physics',
            ],
            [
                'name' => 'Quantum Physics',
                'code' => 'PHY301',
                'department_id' => 3,
                'description' => 'Introduction to quantum mechanics',
            ],
            
            // English subjects
            [
                'name' => 'English Composition',
                'code' => 'ENG101',
                'department_id' => 5,
                'description' => 'Fundamentals of English writing',
            ],
            
            // Islamic Studies subjects
            [
                'name' => 'Islamic History',
                'code' => 'ISL101',
                'department_id' => 6,
                'description' => 'History of Islam and early Islamic civilization',
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
