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
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view business units',
            'create business units',
            'update business units',
            'delete business units',
            'view departments',
            'create departments',
            'update departments',
            'delete departments',
            'view teams',
            'create teams',
            'update teams',
            'delete teams',
            'view positions',
            'create positions',
            'update positions',
            'delete positions',
            'view locations',
            'create locations',
            'update locations',
            'delete locations',
            'view employees',
            'create employees',
            'update employees',
            'delete employees',
            'view skills',
            'create skills',
            'update skills',
            'delete skills',
            'view certifications',
            'create certifications',
            'update certifications',
            'delete certifications',
            'view languages',
            'create languages',
            'update languages',
            'delete languages',
            'view projects',
            'create projects',
            'update projects',
            'delete projects',
            'view assignments',
            'create assignments',
            'update assignments',
            'delete assignments',
            'approve assignments',
            'view recommendations',
            'view users',
            'create users',
            'update users',
            'delete users',
            'view roles',
            'create roles',
            'update roles',
            'delete roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $hr = Role::firstOrCreate(['name' => 'hr']);
        $resourceManager = Role::firstOrCreate(['name' => 'resource-manager']);
        $projectManager = Role::firstOrCreate(['name' => 'project-manager']);
        Role::firstOrCreate(['name' => 'employee']);

        $superAdmin->syncPermissions($permissions);

        $admin->syncPermissions([
            'view employees', 'create employees', 'update employees',
            'view skills', 'create skills', 'update skills',
            'view certifications', 'create certifications', 'update certifications',
            'view languages', 'create languages', 'update languages',
            'view projects', 'create projects', 'update projects',
            'view assignments', 'create assignments', 'update assignments', 'approve assignments',
            'view recommendations',
            'view users', 'create users', 'update users',
            'view roles', 'update roles',
        ]);

        $hr->syncPermissions([
            'view employees', 'create employees', 'update employees',
            'view skills', 'update skills',
            'view certifications', 'update certifications',
            'view languages', 'update languages',
            'view projects',
        ]);

        $resourceManager->syncPermissions([
            'view business units', 'create business units', 'update business units',
            'view departments', 'create departments', 'update departments',
            'view teams', 'create teams', 'update teams',
            'view positions', 'create positions', 'update positions',
            'view locations', 'create locations', 'update locations',
            'view employees', 'create employees', 'update employees',
        ]);

        $projectManager->syncPermissions([
            'view employees',
            'view projects', 'create projects', 'update projects',
            'view assignments', 'create assignments', 'update assignments', 'approve assignments',
            'view recommendations',
        ]);
    }
}
