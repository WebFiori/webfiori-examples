<?php
namespace App\Infrastructure\Repository;

use App\Domain\Post;
use WebFiori\Database\Repository\AbstractRepository;

/**
 * Data access layer for the `posts` table.
 *
 * Includes join queries to fetch author and category names alongside posts.
 */
class PostRepository extends AbstractRepository {
    /**
     * Returns all posts (any status) for admin listing.
     *
     * @return Post[]
     */
    public function findAllWithDetails(): array {
        $sql = "SELECT p.*, a.name AS author_name, c.name AS category_name "
             ."FROM posts p "
             ."LEFT JOIN authors a ON p.author_id = a.id "
             ."LEFT JOIN categories c ON p.category_id = c.id "
             ."ORDER BY p.created_at DESC";

        $result = $this->getDatabase()->raw($sql)->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    /**
     * Finds a post by ID with author and category names.
     */
    public function findByIdWithDetails(int $id): ?Post {
        $sql = "SELECT p.*, a.name AS author_name, c.name AS category_name "
             ."FROM posts p "
             ."LEFT JOIN authors a ON p.author_id = a.id "
             ."LEFT JOIN categories c ON p.category_id = c.id "
             ."WHERE p.id = ?";

        $result = $this->getDatabase()->raw($sql, [$id])->execute();

        if ($result->getRowsCount() === 0) {
            return null;
        }

        return $this->toEntity($result->getRows()[0]);
    }

    /**
     * Finds a post by its URL slug with author and category names.
     */
    public function findBySlug(string $slug): ?Post {
        $sql = "SELECT p.*, a.name AS author_name, c.name AS category_name "
             ."FROM posts p "
             ."LEFT JOIN authors a ON p.author_id = a.id "
             ."LEFT JOIN categories c ON p.category_id = c.id "
             ."WHERE p.slug = ?";

        $result = $this->getDatabase()->raw($sql, [$slug])->execute();

        if ($result->getRowsCount() === 0) {
            return null;
        }

        return $this->toEntity($result->getRows()[0]);
    }

    /**
     * Finds published posts, optionally filtered by category, with pagination.
     *
     * Uses raw SQL to join author and category names.
     *
     * @param int         $page       Page number (1-based).
     * @param int         $perPage    Items per page.
     * @param int|null    $categoryId Filter by category ID.
     *
     * @return array{items: Post[], total: int}
     */
    public function findPublished(int $page = 1, int $perPage = 5, ?int $categoryId = null): array {
        $offset = ($page - 1) * $perPage;

        $where = "p.status = 'published'";
        $params = [];

        if ($categoryId !== null) {
            $where .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }

        $countResult = $this->getDatabase()->raw(
            "SELECT COUNT(*) AS total FROM posts p WHERE $where",
            $params
        )->execute();
        $total = (int) $countResult->getRows()[0]['total'];

        $dbType = $this->getDatabase()->getConnectionInfo()->getDatabaseType();

        if ($dbType === 'mssql') {
            $pageSql = "ORDER BY p.created_at DESC OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
        } else {
            $pageSql = "ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        }

        $sql = "SELECT p.*, a.name AS author_name, c.name AS category_name "
             ."FROM posts p "
             ."LEFT JOIN authors a ON p.author_id = a.id "
             ."LEFT JOIN categories c ON p.category_id = c.id "
             ."WHERE $where "
             .$pageSql;

        if ($dbType === 'mssql') {
            $pageParams = array_merge($params, [$offset, $perPage]);
        } else {
            $pageParams = array_merge($params, [$perPage, $offset]);
        }

        $result = $this->getDatabase()->raw($sql, $pageParams)->execute();

        return [
            'items' => array_map(fn($row) => $this->toEntity($row), $result->fetchAll()),
            'total' => $total
        ];
    }

    /**
     * Finds published posts created since the given date.
     *
     * @return Post[]
     */
    public function findPublishedSince(string $since): array {
        $sql = "SELECT p.*, a.name AS author_name, c.name AS category_name "
             . "FROM posts p "
             . "LEFT JOIN authors a ON p.author_id = a.id "
             . "LEFT JOIN categories c ON p.category_id = c.id "
             . "WHERE p.status = 'published' AND p.created_at >= ? "
             . "ORDER BY p.created_at DESC";

        $result = $this->getDatabase()->raw($sql, [$since])->execute();

        return array_map(fn($row) => $this->toEntity($row), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'posts';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'title' => $entity->title,
            'slug' => $entity->slug,
            'content' => $entity->content,
            'author-id' => $entity->authorId,
            'category-id' => $entity->categoryId,
            'status' => $entity->status,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s'),
            'updated-at' => $entity->updatedAt
        ];
    }

    protected function toEntity(array $row): Post {
        return new Post(
            id: (int) $row['id'],
            title: $row['title'],
            slug: $row['slug'],
            content: $row['content'] ?? '',
            authorId: isset($row['author_id']) ? (int) $row['author_id'] : null,
            categoryId: isset($row['category_id']) ? (int) $row['category_id'] : null,
            status: $row['status'] ?? 'draft',
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null,
            authorName: $row['author_name'] ?? null,
            categoryName: $row['category_name'] ?? null
        );
    }
}
