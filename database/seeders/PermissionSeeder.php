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
        Permission::add('create.api.register', 'api/auth/register', 'POST', 'Create user', 'Auth');
        Permission::add('remove.api.logout', 'api/auth/logout', 'POST', 'Logout user', 'Auth');

        Permission::add('get.api.reports', 'api/reports', 'GET', 'Get reports', 'Reports');

        Permission::add('get.api.users', 'api/users', 'GET', 'Get users', 'Users');
        Permission::add('search.api.users', 'api/users/{user}', 'GET', 'Search user', 'Users');
        Permission::add('update.api.users', 'api/users/{user}', 'PATCH', 'Update user', 'Users');
        Permission::add('delete.api.users', 'api/users/{user}', 'DELETE', 'Delete user', 'Users');

        Permission::add('get.api.add-inventory', 'api/add-inventory', 'GET', 'Get add-inventories', 'Add-inventory');
        Permission::add('create.api.add-inventory', 'api/add-inventory', 'POST', 'Create add-inventory', 'Add-inventory');

        Permission::add('get.api.submit-audit-stock', 'api/submit-audit-stock', 'GET', 'Get audits', 'Submit-audit-stock');
        Permission::add('create.api.submit-audit-stock', 'api/submit-audit-stock', 'POST', 'Create audit', 'Submit-audit-stock');

        Permission::add('get.api.sales', 'api/sales', 'GET', 'Get sales', 'Sales');
        Permission::add('update.api.sales', 'api/sales/{sale}', 'PATCH', 'Create sale', 'Sales');
        Permission::add('delete.api.sales', 'api/sales/{sale}', 'DELETE', 'Delete sale', 'Sales');

        Permission::add('get.api.suppliers', 'api/suppliers', 'GET', 'Get suppliers', 'Suppliers');
        Permission::add('create.api.suppliers', 'api/suppliers', 'POST', 'Create supplier', 'Suppliers');
        Permission::add('update.api.suppliers', 'api/suppliers/{supplier}', 'PATCH', 'Update supplier', 'Suppliers');
        Permission::add('delete.api.suppliers', 'api/suppliers/{supplier}', 'DELETE', 'Delete supplier', 'Suppliers');

        Permission::add('get.api.categories', 'api/categories', 'GET', 'Get categories', 'Categories');
        Permission::add('create.api.categories', 'api/categories', 'POST', 'Create category', 'Categories');
        Permission::add('update.api.categories', 'api/categories/{category}', 'PATCH', 'Update category', 'Categories');
        Permission::add('delete.api.categories', 'api/categories/{category}', 'DELETE', 'Delete category', 'Categories');

        Permission::add('get.api.products', 'api/products', 'GET', 'Get products', 'Products');
        Permission::add('search.api.products', 'api/products/{product}', 'GET', 'Get product', 'Products');
        Permission::add('create.api.products', 'api/products', 'POST', 'Create product', 'Products');
        Permission::add('update.api.products', 'api/products/{product}', 'PATCH', 'Update product', 'Products');
        Permission::add('delete.api.products', 'api/products/{product}', 'DELETE', 'Delete product', 'Products');

        Permission::add('get.api.expenses', 'api/expenses', 'GET', 'Get expenses', 'Expenses');
        Permission::add('search.api.expenses', 'api/expenses/{expense}', 'GET', 'Get expense', 'Expenses');
        Permission::add('create.api.expenses', 'api/expenses', 'POST', 'Create expense', 'Expenses');
        Permission::add('update.api.expenses', 'api/expenses/{expense}', 'PATCH', 'Update expense', 'Expenses');
        Permission::add('delete.api.expenses', 'api/expenses/{expense}', 'DELETE', 'Delete expense', 'Expenses');

    }
}
