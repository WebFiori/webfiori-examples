<?php
namespace App\Domain;

/**
 * Domain entity representing a task (to-do item).
 *
 * This is a plain PHP class with no framework dependencies. It uses
 * constructor promotion to define all properties. The framework's JSON
 * serializer reads public properties directly when building API responses.
 */
class Task {
    /**
     * Creates a new Task instance.
     *
     * @param int|null    $id          Auto-generated primary key. Null for new tasks.
     * @param string      $title       Task title (required).
     * @param string      $description Optional longer description.
     * @param string      $status      Either 'pending' or 'completed'.
     * @param string|null $createdAt   Timestamp when the task was created.
     * @param string|null $updatedAt   Timestamp of the last update, null if never updated.
     */
    public function __construct(
        public ?int $id = null,
        public string $title = '',
        public string $description = '',
        public string $status = 'pending',
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {}

    /**
     * Converts the task to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}
