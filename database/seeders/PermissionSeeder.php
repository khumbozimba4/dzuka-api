<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::add('create.portal.request', 'portal/requests', 'POST', 'Create Portal Request', 'Requests');
        Permission::add('create.api.request', 'api/requests', 'POST', 'Create API Request', 'Requests');
        Permission::add('cancel.api.request', 'api/subscribers/{msisdn}/requests/cancel', 'PATCH', 'Cancel Request From API', 'Requests');
        Permission::add('cancel.portal.request', 'portal/subscribers/{msisdn}/requests/cancel', 'PATCH', 'Cancel Request From Portal', 'Requests');
        Permission::add('get.portal.assignments', 'portal/requests/assignments', 'GET', 'Get Assigned Requests From Portal', 'Requests');
        Permission::add('approve.portal.request', 'portal/requests/{request}/approve', 'PATCH', 'Get Assigned Requests From Portal', 'Requests');

    }
}
