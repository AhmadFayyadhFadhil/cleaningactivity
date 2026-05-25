<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles dan permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftarkan semua permission
        $permissions = [
            // Area
            'manage-areas',
            'view-areas',

            // Schedule
            'manage-schedules',
            'create-schedules',
            'view-schedules',

            // Tasks
            'view-tasks',

            // Checklist
            'manage-checklists',
            'view-checklists',
            'manage-checklist-items',
            'view-checklist-items',

            // Verification
            'verify-schedules',

            // Dashboard
            'view-dashboard',

            // Follow-up
            'manage-follow-ups',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Buat roles dan assign permissions
        $adminRoles = ['admin', 'Admin', 'GA'];
        foreach ($adminRoles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->givePermissionTo(Permission::all());
        }

        $supervisorRoles = ['supervisor', 'Supervisor'];
        foreach ($supervisorRoles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->givePermissionTo([
                'view-areas',
                'view-schedules',
                'view-tasks',
                'view-checklists',
                'view-checklist-items',
                'verify-schedules',
                'view-dashboard',
                'manage-follow-ups',
            ]);
        }

        $staffRoles = ['staff', 'Cleaning Service', 'PIC Area'];
        foreach ($staffRoles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->givePermissionTo([
                'view-tasks',
                'view-checklists',
                'view-checklist-items',
            ]);
        }
    }
}