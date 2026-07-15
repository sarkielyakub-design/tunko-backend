<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\Admin;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $roles = [

            'Super Admin',

            'Finance',

            'Support',

            'Compliance',

            'Operations',

            'Marketing',

        ];

        foreach ($roles as $role) {

            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'admin',
            ]);

        }

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [

            'dashboard.view',

            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'wallet.view',
            'wallet.credit',
            'wallet.debit',

            'transfer.view',
            'transfer.manage',

            'airtime.view',
            'airtime.manage',

            'data.view',
            'data.manage',

            'kyc.view',
            'kyc.approve',

            'office.view',
            'office.create',
            'office.update',
            'office.delete',

            'country.manage',

            'network.manage',

            'settings.manage',

            'reports.view',

            'admins.manage',

            'roles.manage',

        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin',
            ]);

        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin Role
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::findByName(
            'Super Admin',
            'admin'
        );

        $superAdmin->syncPermissions(
            Permission::where(
                'guard_name',
                'admin'
            )->get()
        );

        /*
        |--------------------------------------------------------------------------
        | Admin Account
        |--------------------------------------------------------------------------
        */

        $admin = Admin::updateOrCreate(

            [
                'email' => 'admin@tunko.com',
            ],

            [
                'name' => 'Super Administrator',

                'phone' => '+227000000000',

                'password' => Hash::make(
                    'Admin@123456'
                ),

                'status' => true,
            ]

        );

        $admin->assignRole(
            'Super Admin'
        );
    }
}