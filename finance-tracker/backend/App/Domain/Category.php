<?php
namespace App\Domain;

class Category {
    public function __construct(
        public ?int $id = null,
        public ?int $userId = null,
        public string $name = '',
        public string $type = 'expense',
        public string $icon = '',
        public string $color = '#333333'
    ) {
    }
}
