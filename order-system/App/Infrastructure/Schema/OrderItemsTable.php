<?php
namespace App\Infrastructure\Schema;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

#[Table(name: 'order_items')]
class OrderItemsTable {
    #[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true, identity: true)]
    private int $id;
    #[Column(name: 'order_id', type: DataType::INT)]
    private int $orderId;
    #[Column(name: 'product_id', type: DataType::INT)]
    private int $productId;
    #[Column(name: 'quantity', type: DataType::INT, default: 1)]
    private int $quantity;
    #[Column(name: 'unit_price', type: DataType::DECIMAL, size: 10, scale: 2)]
    private float $unitPrice;
}
