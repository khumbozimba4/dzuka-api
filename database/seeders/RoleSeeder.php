<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("roles")->insert([
            [
                'name' => 'admin',
                'display_name' => 'User Administrator', // optional
                'description' => 'User is allowed to manage and edit other users', // optional
            ],
            [
                'name' => 'finance',
                'display_name' => 'Finance User', // optional
                'description' => 'Financial auditor', // optional
            ],
            [
                'name' => 'operations',
                'display_name' => 'Operations User', // optional
                'description' => 'Operations user', // optional
            ],
        ]);

    }
}
