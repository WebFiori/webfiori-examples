<?php
namespace App\Database\Seeders;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class SeedSampleData extends AbstractMigration {
    public function down(Database $db): void {
    }

    public function up(Database $db): void {
        // Tenants
        $db->table('tenants')->insert(['name' => 'Acme Corp', 'plan' => 'pro', 'created-at' => date('Y-m-d H:i:s')])->execute();
        $db->table('tenants')->insert(['name' => 'Startup Inc', 'plan' => 'free', 'created-at' => date('Y-m-d H:i:s')])->execute();

        // Users
        $users = [
            [0, 'Super Admin', 'super@platform.com', password_hash('super123', PASSWORD_BCRYPT), 'super-admin'],
            [1, 'Acme Admin', 'admin@acme.com', password_hash('acme123', PASSWORD_BCRYPT), 'tenant-admin'],
            [1, 'Acme Member', 'member@acme.com', password_hash('member123', PASSWORD_BCRYPT), 'member'],
            [2, 'Startup Admin', 'admin@startup.com', password_hash('startup123', PASSWORD_BCRYPT), 'tenant-admin'],
        ];

        foreach ($users as [$tenantId, $name, $email, $hash, $role]) {
            $db->table('users')->insert([
                'tenant-id' => $tenantId,
                'name' => $name,
                'email' => $email,
                'password-hash' => $hash,
                'role' => $role,
                'active' => true,
            ])->execute();
        }

        // Subscriptions
        $db->table('subscriptions')->insert([
            'tenant-id' => 1, 'plan' => 'pro', 'status' => 'active',
            'starts-at' => date('Y-m-d H:i:s', strtotime('-30 days')),
            'expires-at' => date('Y-m-d H:i:s', strtotime('+335 days')),
        ])->execute();

        $db->table('subscriptions')->insert([
            'tenant-id' => 2, 'plan' => 'free', 'status' => 'active',
            'starts-at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'expires-at' => date('Y-m-d H:i:s', strtotime('+355 days')),
        ])->execute();

        // Sample invoice
        $db->table('invoices')->insert([
            'tenant-id' => 1, 'amount' => 99.00, 'status' => 'paid',
            'period' => '2026-04', 'created-at' => date('Y-m-d H:i:s', strtotime('-30 days')),
        ])->execute();
    }
}
