<?php

namespace Tests;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * Buat user dengan permission tertentu.
     */
    protected function userWithPermission(string|array $permissions): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->givePermissionTo($permissions);
        return $user;
    }

    /**
     * Buat user dengan role tertentu.
     */
    protected function userWithRole(string $roleName): \App\Models\User
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user = \App\Models\User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    /**
     * Pastikan permission tersedia (dibuat jika belum ada).
     */
    protected function ensurePermissionsExist(array $permissions): void
    {
        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
                'guard_name' => 'web',
            ]);
        }
    }
}
