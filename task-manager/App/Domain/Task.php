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
    /** Valid status values for a task. */
    public const VALID_STATUSES = ['pending', 'in-progress', 'completed'];

    /** Valid priority values for a task. */
    public const VALID_PRIORITIES = ['low', 'medium', 'high'];

    /**
     * Creates a new Task instance.
     *
     * @param int|null    $id          Auto-generated primary key. Null for new tasks.
     * @param string      $title       Task title (required).
     * @param string      $description Optional longer description.
     * @param string      $status      One of: pending, in-progress, completed.
     * @param string      $priority    One of: low, medium, high.
     * @param string|null $dueDate     Optional due date (Y-m-d H:i:s format).
     * @param string|null $createdAt   Timestamp when the task was created.
     * @param string|null $updatedAt   Timestamp of the last update, null if never updated.
     */
    public function __construct(
        public ?int $id = null,
        public string $title = '',
        public string $description = '',
        public string $status = 'pending',
        public string $priority = 'medium',
        public ?string $dueDate = null,
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
            'priority' => $this->priority,
            'dueDate' => $this->dueDate,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}
