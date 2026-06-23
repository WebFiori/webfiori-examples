<?php
namespace App\Database\Seeders;

use App\Database\Migrations\CreateProductsTable;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractSeeder;

class SeedProducts extends AbstractSeeder {

    public function getDependencies(): array {
        return [CreateProductsTable::class];
    }

    public function run(Database $db): void {
        $products = [
            ['name' => 'Wireless Keyboard', 'category' => 'Electronics', 'price' => 49.99, 'stock' => 100],
            ['name' => 'Standing Desk', 'category' => 'Furniture', 'price' => 399.00, 'stock' => 25],
            ['name' => 'USB-C Hub', 'category' => 'Electronics', 'price' => 29.99, 'stock' => 200],
            ['name' => 'Monitor Arm', 'category' => 'Furniture', 'price' => 89.99, 'stock' => 50],
            ['name' => 'Mechanical Keyboard', 'category' => 'Electronics', 'price' => 129.99, 'stock' => 75],
            ['name' => 'Ergonomic Chair', 'category' => 'Furniture', 'price' => 599.00, 'stock' => 15],
            ['name' => 'Webcam HD', 'category' => 'Electronics', 'price' => 79.99, 'stock' => 120],
            ['name' => 'Desk Lamp', 'category' => 'Furniture', 'price' => 45.00, 'stock' => 80],
            ['name' => 'Noise Cancelling Headphones', 'category' => 'Electronics', 'price' => 249.99, 'stock' => 60],
            ['name' => 'Cable Management Kit', 'category' => 'Accessories', 'price' => 19.99, 'stock' => 300],
        ];

        foreach ($products as $product) {
            $db->table('products')->insert($product)->execute();
        }
    }
}
