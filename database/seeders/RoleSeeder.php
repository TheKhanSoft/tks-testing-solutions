<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin',
            'faculty',
            'staff',
            'student',
            'candidate',
            'examiner',
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }
    }
}
