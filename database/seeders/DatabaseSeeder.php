<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Main application seeders
        $this->call([
            UserSeeder::class,
            RoleSeeder::class,
            PermissionsSeeder::class,
            
            // Department and subjects
            DepartmentSeeder::class,
            SubjectSeeder::class,
            DepartmentSubjectSeeder::class,
            
            // Faculty members and subject assignments
            FacultyMemberSeeder::class,
            FacultySubjectSeeder::class,
            
            // Question types and questions
            QuestionTypeSeeder::class,
            QuestionSeeder::class,
            QuestionOptionSeeder::class,
            
            // Papers and categories
            PaperCategorySeeder::class,
            PaperSeeder::class,
            PaperSubjectSeeder::class,
            PaperQuestionSeeder::class,

            // Candidate and Paper
            CandidateSeeder::class,
            TestAttemptSeeder::class,
            CandidatePaperSeeder::class,
    
        ]);
    }
}
