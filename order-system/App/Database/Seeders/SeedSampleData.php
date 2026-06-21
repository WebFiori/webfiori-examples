<?php
namespace App\Database\Seeders;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class SeedSampleData extends AbstractMigration {
    public function down(Database $db): void {
    }

    public function up(Database $db): void {
        // Users
        $users = [
            ['Admin User', 'admin@example.com', password_hash('admin123', PASSWORD_BCRYPT), 'admin'],
            ['Staff Member', 'staff@example.com', password_hash('staff123', PASSWORD_BCRYPT), 'staff'],
            ['John Customer', 'john@example.com', password_hash('john123', PASSWORD_BCRYPT), 'customer'],
            ['Jane Customer', 'jane@example.com', password_hash('jane123', PASSWORD_BCRYPT), 'customer'],
        ];

        foreach ($users as [$name, $email, $hash, $role]) {
            $db->table('users')->insert([
                'name' => $name,
                'email' => $email,
                'password-hash' => $hash,
                'role' => $role,
                'active' => true,
            ])->execute();
        }

        // Products
        $products = [
            ['Wireless Keyboard', 'Bluetooth mechanical keyboard', 79.99, 50],
            ['USB-C Hub', '7-in-1 USB-C adapter', 49.99, 100],
            ['Monitor Stand', 'Adjustable aluminum stand', 129.99, 30],
            ['Webcam HD', '1080p webcam with mic', 59.99, 75],
            ['Desk Lamp', 'LED desk lamp with dimmer', 39.99, 200],
        ];

        foreach ($products as [$name, $desc, $price, $stock]) {
            $db->table('products')->insert([
                'name' => $name,
                'description' => $desc,
                'price' => $price,
                'stock' => $stock,
                'created-at' => date('Y-m-d H:i:s'),
            ])->execute();
        }
    }
}
