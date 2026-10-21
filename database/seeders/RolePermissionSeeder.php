<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Master Data
            'view categories',
            'manage categories',

            'view units',
            'manage units',

            'view products',
            'manage products',

            'view suppliers',
            'manage suppliers',

            'view warehouses',
            'manage warehouses',

            'view locations',
            'manage locations',

            // Inventory Transactions
            'view goods receipts',
            'create goods receipts',

            'view stock outs',
            'create stock outs',

            'view stock transfers',
            'create stock transfers',

            'view stock adjustments',
            'create stock adjustments',

            'view stock opnames',
            'create stock opnames',

            // Monitoring
            'view current stock',
            'view stock movements',
            'view low stock',

            // Reports
            'view stock report',
            'export stock report',

            'view movement report',
            'export movement report',

            // System
            'manage users',
            'manage roles',
            'view activity logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // ADMIN
        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions(Permission::all());

        // WAREHOUSE STAFF
        $warehouseStaff = Role::firstOrCreate([
            'name' => 'Warehouse Staff',
            'guard_name' => 'web',
        ]);

        $warehouseStaff->syncPermissions([
            'view categories',
            'view units',
            'view products',
            'view suppliers',
            'view warehouses',
            'view locations',

            'view goods receipts',
            'create goods receipts',

            'view stock outs',
            'create stock outs',

            'view stock transfers',
            'create stock transfers',

            'view stock adjustments',
            'create stock adjustments',

            'view stock opnames',
            'create stock opnames',

            'view current stock',
            'view stock movements',
            'view low stock',

            'view stock report',
            'export stock report',

            'view movement report',
            'export movement report',
        ]);

        // VIEWER
        $viewer = Role::firstOrCreate([
            'name' => 'Viewer',
            'guard_name' => 'web',
        ]);

        $viewer->syncPermissions([
            'view categories',
            'view units',
            'view products',
            'view suppliers',
            'view warehouses',
            'view locations',

            'view goods receipts',
            'view stock outs',
            'view stock transfers',
            'view stock adjustments',
            'view stock opnames',

            'view current stock',
            'view stock movements',
            'view low stock',

            'view stock report',
            'export stock report',

            'view movement report',
            'export movement report',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}