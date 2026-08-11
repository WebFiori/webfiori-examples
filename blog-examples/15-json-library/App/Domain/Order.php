<?php
namespace App\Domain;

use WebFiori\Json\JsonType;

/**
 * An order with a nested customer object and an array of line items.
 * Demonstrates #[JsonType] for typed deserialization of nested objects.
 */
class Order {
    private int $id;
    private Customer $customer;
    /** @var LineItem[] */
    private array $items;
    private string $status;

    public function __construct(
        int $id,
        Customer $customer,
        #[JsonType(LineItem::class, isArray: true)]
        array $items,
        string $status = 'pending'
    ) {
        $this->id = $id;
        $this->customer = $customer;
        $this->items = $items;
        $this->status = $status;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getCustomer(): Customer {
        return $this->customer;
    }

    /**
     * @return LineItem[]
     */
    public function getItems(): array {
        return $this->items;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getTotal(): float {
        return array_sum(array_map(fn(LineItem $i) => $i->getSubtotal(), $this->items));
    }
}
