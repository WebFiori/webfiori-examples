<?php
namespace App\Database\Seeders;

use App\Database\Migrations\CreateShortUrlsTable;
use App\Domain\ShortLink;
use App\Infrastructure\Repository\ShortLinkRepository;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractSeeder;

class SeedSampleLinks extends AbstractSeeder {
    public function getDependencies(): array {
        return [CreateShortUrlsTable::class];
    }
    public function getEnvironments(): array {
        return ['dev', 'test'];
    }

    public function run(Database $db): void {
        $repo = new ShortLinkRepository($db);
        $now = date('Y-m-d H:i:s');
        $urls = [
            ['https://webfiori.com', 'abc123'],
            ['https://github.com/WebFiori/framework', 'gh0001'],
            ['https://example.com/very/long/url/that/needs/shortening', 'ex0001'],
        ];

        foreach ($urls as [$url, $code]) {
            $repo->insert(new ShortLink(
                id: $code,
                redirectTo: $url,
                ipAddress: '127.0.0.1',
                createdAt: $now
            ));
        }
    }
}
