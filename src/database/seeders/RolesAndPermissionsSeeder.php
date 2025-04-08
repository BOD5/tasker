<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::findOrCreate('define team custom fields', 'web');
        Permission::findOrCreate('fill custom fields', 'web');

        Permission::findOrCreate('manage teams', 'web');
        Permission::findOrCreate('manage team settings', 'web');
        Permission::findOrCreate('manage team members', 'web');

        Permission::findOrCreate('manage projects', 'web');

        Permission::findOrCreate('manage tasks', 'web');
        Permission::findOrCreate('assign tasks', 'web');
        Permission::findOrCreate('view team tasks', 'web');
        Permission::findOrCreate('view own assigned tasks', 'web');

        Permission::findOrCreate('track time', 'web');
        Permission::findOrCreate('edit own time entries', 'web');
        Permission::findOrCreate('delete own time entries', 'web');
        Permission::findOrCreate('view team time entries', 'web');

        Permission::findOrCreate('view team reports', 'web');
        Permission::findOrCreate('view own reports', 'web');

        Permission::findOrCreate('manage users', 'web');

        $adminRole = Role::findOrCreate('admin', 'web');
        $managerRole = Role::findOrCreate('manager', 'web');
        $workerRole = Role::findOrCreate('worker', 'web');

        $managerRole->givePermissionTo([
            'manage team settings',
            'manage team members',
            'manage projects',
            'manage tasks',
            'assign tasks',
            'view team tasks',
            'view own assigned tasks',
            'track time',
            'edit own time entries',
            'delete own time entries',
            'view team time entries',
            'define team custom fields',
            'fill custom fields',
            'view team reports',
            'view own reports',
        ]);

        $workerRole->givePermissionTo([
            'view own assigned tasks',
            'track time',
            'edit own time entries',
            'delete own time entries',
            'fill custom fields',
            'view own reports',
        ]);

        $adminRole->givePermissionTo([
            'manage users',
            'manage teams',
            'manage projects',
            'manage tasks',
        ]);

        $this->command->info('Ролі та права успішно створено та призначено.');
    }
}
