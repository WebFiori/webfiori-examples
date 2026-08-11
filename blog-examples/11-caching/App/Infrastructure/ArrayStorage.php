<?php
namespace App\Infrastructure;

use WebFiori\Cache\Cache;
use WebFiori\Cache\Storage;
use WebFiori\Cache\Item;

/**
 * In-memory cache storage driver — for testing only.
 * Stores items in a plain array with no file I/O.
 */
class ArrayStorage implements Storage {
    private array $store = [];

    public function store(Item $item): void {
        $key = ($item->getPrefix() ?? '') . $item->getKey();
        $this->store[$key] = [
            'item'    => $item,
            'expires' => $item->getExpiryTime(),
        ];
    }

    public function read(string $key, ?string $prefix): mixed {
        $item = $this->readItem($key, $prefix);

        return $item?->getDataDecrypted();
    }

    public function readItem(string $key, ?string $prefix): ?Item {
        $fullKey = ($prefix ?? '') . $key;

        if (!isset($this->store[$fullKey])) {
            return null;
        }

        if (time() > $this->store[$fullKey]['expires']) {
            unset($this->store[$fullKey]);

            return null;
        }

        return $this->store[$fullKey]['item'];
    }

    public function has(string $key, ?string $prefix): bool {
        return $this->readItem($key, $prefix) !== null;
    }

    public function delete(string $key): void {
        unset($this->store[$key]);
    }

    public function flush(?string $prefix): void {
        if ($prefix === null || $prefix === '') {
            $this->store = [];

            return;
        }

        foreach (array_keys($this->store) as $key) {
            if (str_starts_with($key, $prefix)) {
                unset($this->store[$key]);
            }
        }
    }

    public function purgeExpired(): int {
        $now = time();
        $removed = 0;

        foreach ($this->store as $key => $entry) {
            if ($now > $entry['expires']) {
                unset($this->store[$key]);
                $removed++;
            }
        }

        return $removed;
    }

    public function count(): int {
        return count($this->store);
    }
}
