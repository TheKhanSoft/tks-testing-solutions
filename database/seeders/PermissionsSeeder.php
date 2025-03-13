<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'user_access',
            'user_create',
            'user_edit',
            'user_delete',
            
            // Department management
            'department_access',
            'department_create',
            'department_edit',
            'department_delete',
            
            // Subject management
            'subject_access',
            'subject_create',
            'subject_edit',
            'subject_delete',
            
            // Question management
            'question_access',
            'question_create',
            'question_edit',
            'question_delete',
            
            // Paper management
            'paper_access',
            'paper_create',
            'paper_edit',
            'paper_delete',
            
            // Candidate management
            'candidate_access',
            'candidate_create',
            'candidate_edit',
            'candidate_delete',
            
            // Test attempt management
            'test_attempt_access',
            'test_attempt_create',
            'test_attempt_edit',
            'test_attempt_delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign all permissions to admin
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());
        
        // Assign appropriate permissions to faculty
        $facultyRole = Role::findByName('faculty');
        $facultyRole->givePermissionTo([
            'question_access',
            'question_create',
            'question_edit',
            'paper_access',
            'paper_create',
            'paper_edit',
        ]);
        
        // Assign user roles
        // User::where('email', 'admin@example.com')->first()->assignRole('admin');
        // User::where('email', 'ahsan@example.com')->first()->assignRole('faculty');
        // User::where('email', 'ayesha@example.com')->first()->assignRole('staff');
    }
}
