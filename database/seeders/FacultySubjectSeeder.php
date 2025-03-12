<?php

namespace Database\Seeders;

use App\Models\FacultySubject;
use Illuminate\Database\Seeder;

class FacultySubjectSeeder extends Seeder
{
    public function run(): void
    {
        $facultySubjects = [
            // Dr. Asif Iqbal's subjects
            [
                'faculty_member_id' => 1,
                'subject_id' => 1, // Programming Fundamentals
            ],
            [
                'faculty_member_id' => 1,
                'subject_id' => 3, // Database Systems
            ],
            
            // Prof. Fatima Zahra's subjects
            [
                'faculty_member_id' => 2,
                'subject_id' => 2, // Data Structures
            ],
            
            // Dr. Imran Malik's subjects
            [
                'faculty_member_id' => 3,
                'subject_id' => 4, // Calculus
            ],
            [
                'faculty_member_id' => 3,
                'subject_id' => 5, // Linear Algebra
            ],
            
            // Prof. Rabia Hassan's subjects
            [
                'faculty_member_id' => 4,
                'subject_id' => 6, // Classical Mechanics
            ],
            [
                'faculty_member_id' => 4,
                'subject_id' => 7, // Quantum Physics
            ],
            
            // Dr. Ahmed Khan's subjects
            [
                'faculty_member_id' => 5,
                'subject_id' => 8, // English Composition
            ],
            
            // Prof. Maryam Nawaz's subjects
            [
                'faculty_member_id' => 6,
                'subject_id' => 9, // Islamic History
            ],
        ];

        foreach ($facultySubjects as $record) {
            FacultySubject::create($record);
        }
    }
}
