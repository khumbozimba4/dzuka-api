<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PermissionSeeder::class);

        $admin = Role::create([
            'name' => 'Admin'
        ]);

        $operations = Role::create([
            'name' => 'Operations'
        ]);

        $finance = Role::create([
            'name' => 'Finance'
        ]);

        $admin->permissions()->attach(Permission::all());
        $operations->permissions()->attach(Permission::findByOperations());
        $finance->permissions()->attach(Permission::findByFinance());
    }
}
