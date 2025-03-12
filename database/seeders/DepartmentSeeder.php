<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Computer Science',
                'description' => 'Department of Computer Science and Information Technology',
            ],
            [
                'name' => 'Mathematics',
                'description' => 'Department of Mathematical Sciences',
            ],
            [
                'name' => 'Physics',
                'description' => 'Department of Physics and Astronomy',
            ],
            [
                'name' => 'Chemistry',
                'description' => 'Department of Chemistry and Biochemistry',
            ],
            [
                'name' => 'English',
                'description' => 'Department of English Language and Literature',
            ],
            [
                'name' => 'Islamic Studies',
                'description' => 'Department of Islamic Studies and Theology',
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
