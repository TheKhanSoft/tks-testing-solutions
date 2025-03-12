<?php

namespace Database\Seeders;

use App\Models\FacultyMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FacultyMemberSeeder extends Seeder
{
    public function run(): void
    {
        $facultyMembers = [
            [
                'name' => 'Dr. Asif Iqbal',
                'email' => 'asif.iqbal@faculty.edu',
                'password' => Hash::make('password'),
                'department_id' => 1, // Computer Science
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Prof. Fatima Zahra',
                'email' => 'fatima.zahra@faculty.edu',
                'password' => Hash::make('password'),
                'department_id' => 1, // Computer Science
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Dr. Imran Malik',
                'email' => 'imran.malik@faculty.edu',
                'password' => Hash::make('password'),
                'department_id' => 2, // Mathematics
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Prof. Rabia Hassan',
                'email' => 'rabia.hassan@faculty.edu',
                'password' => Hash::make('password'),
                'department_id' => 3, // Physics
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Dr. Ahmed Khan',
                'email' => 'ahmed.khan@faculty.edu',
                'password' => Hash::make('password'),
                'department_id' => 5, // English
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Prof. Maryam Nawaz',
                'email' => 'maryam.nawaz@faculty.edu',
                'password' => Hash::make('password'),
                'department_id' => 6, // Islamic Studies
                'email_verified_at' => now(),
            ],
        ];

        foreach ($facultyMembers as $member) {
            FacultyMember::create($member);
        }
    }
}
