<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Class RolesAndPermissionsSeeder
 * @package Database\Seeders
 */
class RolesAndPermissionsSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (config('roles')['permissions'] as $permissionName => $description) {
            Permission::query()->firstOrCreate([
                'guard_name' => config('auth.defaults.guard'),
                'name' => $permissionName,
                'description' => $description['en']
            ]);
        }

        Role::all()->map(function ($role) {
            $permissions = config('roles')['roles'][$role->name]['permissions'];
            if (count($permissions)) {
                collect($permissions)->map(function ($permission) use ($role) {
                    $role->givePermissionTo($permission);
                });
            }
        });
    }
}
