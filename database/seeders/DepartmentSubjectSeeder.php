<?php

namespace Database\Seeders;

use App\Models\DepartmentSubject;
use Illuminate\Database\Seeder;

class DepartmentSubjectSeeder extends Seeder
{
    public function run(): void
    {
        // The associations are already created in SubjectSeeder through the department_id field,
        // but we can create additional cross-department associations here if needed
        
        $departmentSubjects = [
            // Mathematics department also teaches calculus to Physics department
            [
                'department_id' => 3, // Physics department
                'subject_id' => 4,    // Calculus
            ],
            // Computer Science department also shares some math courses
            [
                'department_id' => 1, // CS department
                'subject_id' => 5,    // Linear Algebra
            ],
        ];

        foreach ($departmentSubjects as $record) {
            DepartmentSubject::create($record);
        }
    }
}
