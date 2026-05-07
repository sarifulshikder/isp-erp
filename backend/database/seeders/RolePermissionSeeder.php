<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions তৈরি
        $permissions = [
            // Customers
            'view customers', 'create customers', 'edit customers', 'delete customers',
            // Invoices
            'view invoices', 'create invoices', 'edit invoices', 'delete invoices',
            // Payments
            'view payments', 'create payments', 'edit payments', 'delete payments',
            // Packages
            'view packages', 'create packages', 'edit packages', 'delete packages',
            // MikroTik
            'view mikrotik', 'manage mikrotik',
            // Reports
            'view reports',
            // Settings
            'manage settings',
            // Users
            'view users', 'create users', 'edit users', 'delete users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles তৈরি ও permissions assign
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'view customers', 'create customers', 'edit customers',
            'view invoices', 'create invoices', 'edit invoices',
            'view payments', 'create payments', 'edit payments',
            'view packages', 'view mikrotik',
            'view reports',
        ]);

        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->givePermissionTo([
            'view customers', 'create customers', 'edit customers',
            'view invoices', 'create invoices',
            'view payments', 'create payments',
            'view packages',
        ]);

        // Admin user কে super_admin role দাও
        $admin = User::where('email', 'sarifulshikder@gmail.com')->first();
        if ($admin) {
            $admin->assignRole('super_admin');
            echo "✅ super_admin role assigned to: " . $admin->email . "\n";
        }

        echo "✅ Roles & Permissions created successfully!\n";
    }
}
