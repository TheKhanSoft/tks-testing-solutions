<?php

namespace Database\Seeders;

use App\Models\Candidate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = [
            [
                'name' => 'Usman Ali',
                'father_name' => 'Muhammad Ali',
                'gender' => 'male',
                'dob' => '1998-05-12',
                'id_card_no' => '35201-1234567-1',
                'password' => Hash::make('password'),
                'email' => 'usman.ali@example.com',
                'phone' => '03001234567',
                'address' => 'House #123, Block A, Gulberg III, Lahore',
                'status' => 'active',
            ],
            [
                'name' => 'Aisha Bibi',
                'father_name' => 'Abdul Rehman',
                'gender' => 'female',
                'dob' => '1999-03-22',
                'id_card_no' => '35201-7654321-2',
                'password' => Hash::make('password'),
                'email' => 'aisha.bibi@example.com',
                'phone' => '03007654321',
                'address' => 'Flat #5, Al-Faisal Apartments, Johar Town, Lahore',
                'status' => 'active',
            ],
            [
                'name' => 'Mohammad Bilal',
                'father_name' => 'Mohammad Akram',
                'gender' => 'male',
                'dob' => '1997-11-15',
                'id_card_no' => '42101-1122334-5',
                'password' => Hash::make('password'),
                'email' => 'bilal.akram@example.com',
                'phone' => '03331122334',
                'address' => '45-C, Block 6, PECHS, Karachi',
                'status' => 'active',
            ],
            [
                'name' => 'Fatima Zahra',
                'father_name' => 'Syed Hassan',
                'gender' => 'female',
                'dob' => '2000-01-30',
                'id_card_no' => '37401-5544332-1',
                'password' => Hash::make('password'),
                'email' => 'fatima.zahra@example.com',
                'phone' => '03045544332',
                'address' => 'House #789, Street 4, F-10/3, Islamabad',
                'status' => 'active',
            ],
            [
                'name' => 'Hamza Khan',
                'father_name' => 'Imtiaz Khan',
                'gender' => 'male',
                'dob' => '1996-07-25',
                'id_card_no' => '33100-9988776-5',
                'password' => Hash::make('password'),
                'email' => 'hamza.khan@example.com',
                'phone' => '03119988776',
                'address' => '123-A, Phase 1, DHA, Lahore',
                'status' => 'active',
            ],
            [
                'name' => 'Sadia Malik',
                'father_name' => 'Tariq Malik',
                'gender' => 'female',
                'dob' => '1998-12-10',
                'id_card_no' => '42201-3322114-4',
                'password' => Hash::make('password'),
                'email' => 'sadia.malik@example.com',
                'phone' => '03213322114',
                'address' => 'Flat #12, Al-Habib Heights, Gulshan-e-Iqbal, Karachi',
                'status' => 'active',
            ],
        ];

        foreach ($candidates as $candidate) {
            Candidate::create($candidate);
        }
    }
}
