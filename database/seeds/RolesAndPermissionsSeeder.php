<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create permissions
        Permission::create(['name' => 'user-management']);
        Permission::create(['name' => 'import-data']);
        Permission::create(['name' => 'import-orders']);

        // Create roles and assign permissions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo('user-management');
        $admin->givePermissionTo('import-data');

        $editor = Role::create(['name' => 'member']);
        $editor->givePermissionTo('import-data');
    }
}
