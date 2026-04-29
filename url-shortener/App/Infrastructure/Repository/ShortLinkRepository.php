<?php
namespace App\Infrastructure\Repository;

use App\Domain\ShortLink;
use WebFiori\Database\Repository\AbstractRepository;

/**
 * Data access layer for the `short_urls` table.
 */
class ShortLinkRepository extends AbstractRepository {
    /**
     * Removes expired links and returns the count deleted.
     */
    public function deleteExpired(): int {
        $result = $this->getDatabase()
            ->table($this->getTableName())
            ->select()
            ->where('expires-at', date('Y-m-d H:i:s'), '<=')
            ->execute();

        $count = $result->getRowsCount();

        if ($count > 0) {
            $this->getDatabase()
                ->table($this->getTableName())
                ->delete()
                ->where('expires-at', date('Y-m-d H:i:s'), '<=')
                ->execute();
        }

        return $count;
    }

    /**
     * Finds a link by its original URL.
     */
    public function findByRedirectTo(string $url): ?ShortLink {
        $result = $this->getDatabase()
            ->table($this->getTableName())
            ->select()
            ->where('redirect-to', trim($url))
            ->execute();

        return $result->getRowsCount() === 1 ? $this->toEntity($result->getRows()[0]) : null;
    }

    /**
     * Generates a random 6-character short code.
     */
    public function generateShortId(): string {
        $short = '';

        while (strlen($short) < 6) {
            $hash = hash('sha256', microtime(true).random_int(0, 99999));
            $char = $hash[random_int(0, strlen($hash) - 1)];
            $short .= random_int(0, 1) ? strtoupper($char) : $char;
        }

        return $short;
    }

    /**
     * Increments click count and returns updated link.
     */
    public function incrementClicks(string $id): ?ShortLink {
        $link = $this->findById($id);

        if ($link !== null) {
            $this->getDatabase()
                ->table($this->getTableName())
                ->update(['number-of-clicks' => $link->numberOfClicks + 1])
                ->where('id', $id)
                ->execute();
            $link->numberOfClicks++;
        }

        return $link;
    }

    /**
     * Inserts a new short link (ID is pre-generated, not auto-increment).
     */
    public function insert(ShortLink $entity): void {
        $data = $this->toArray($entity);
        $this->getDatabase()->table($this->getTableName())->insert($data)->execute();
    }

    /**
     * Returns top N most clicked links.
     *
     * @return ShortLink[]
     */
    public function topClicked(int $limit = 10): array {
        $dbType = $this->getDatabase()->getConnectionInfo()->getDatabaseType();

        if ($dbType === 'mssql') {
            $sql = "SELECT TOP $limit * FROM short_urls ORDER BY number_of_clicks DESC";
        } else {
            $sql = "SELECT * FROM short_urls ORDER BY number_of_clicks DESC LIMIT $limit";
        }

        $result = $this->getDatabase()->raw($sql)->execute();

        return array_map(fn ($row) => $this->toEntity($row), $result->fetchAll());
    }

    protected function getIdField(): string {
        return 'id';
    }
    protected function getTableName(): string {
        return 'short_urls';
    }

    protected function toArray(object $entity): array {
        return [
            'id' => $entity->id,
            'redirect-to' => $entity->redirectTo,
            'ip-address' => $entity->ipAddress,
            'number-of-clicks' => $entity->numberOfClicks,
            'created-at' => $entity->createdAt ?? date('Y-m-d H:i:s'),
            'expires-at' => $entity->expiresAt,
        ];
    }

    protected function toEntity(array $row): ShortLink {
        return new ShortLink(
            id: $row['id'],
            redirectTo: $row['redirect_to'],
            ipAddress: $row['ip_address'] ?? '',
            numberOfClicks: (int) ($row['number_of_clicks'] ?? 0),
            createdAt: $row['created_at'] ?? null,
            expiresAt: $row['expires_at'] ?? null
        );
    }
}
