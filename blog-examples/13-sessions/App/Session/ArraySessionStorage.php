<?php
namespace App\Session;

use WebFiori\Framework\Session\SessionStorage;

/**
 * In-memory session storage for testing.
 * Stores session data in a plain array with no file or database I/O.
 */
class ArraySessionStorage implements SessionStorage {
    private array $store = [];

    public function save(string $sessionId, string $serializedSession): void {
        $this->store[$sessionId] = [
            'data'  => $serializedSession,
            'saved' => time(),
        ];
    }

    public function read(string $sessionId): ?string {
        return $this->store[$sessionId]['data'] ?? null;
    }

    public function remove(string $sessionId): void {
        unset($this->store[$sessionId]);
    }

    public function gc(string $olderThan, int $maxCount = 0): void {
        $threshold = strtotime($olderThan);
        $removed = 0;

        foreach ($this->store as $id => $entry) {
            if ($maxCount > 0 && $removed >= $maxCount) {
                break;
            }

            if ($entry['saved'] < $threshold) {
                unset($this->store[$id]);
                $removed++;
            }
        }
    }

    public function count(): int {
        return count($this->store);
    }

    public function has(string $sessionId): bool {
        return isset($this->store[$sessionId]);
    }
}
