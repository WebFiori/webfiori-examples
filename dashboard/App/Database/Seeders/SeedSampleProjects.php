<?php
namespace App\Database\Seeders;

use App\Database\Migrations\CreateDashboardTables;
use App\Domain\Project;
use App\Infrastructure\Repository\ProjectRepository;
use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractSeeder;

class SeedSampleProjects extends AbstractSeeder {
    public function getDependencies(): array {
        return [CreateDashboardTables::class];
    }
    public function getEnvironments(): array {
        return ['dev', 'test'];
    }

    public function run(Database $db): void {
        $repo = new ProjectRepository($db);
        $now = date('Y-m-d H:i:s');

        $projects = [
            new Project(name: 'Website Redesign', description: 'Redesign the company website.', status: 'active', ownerId: 2, createdAt: $now),
            new Project(name: 'Mobile App', description: 'Build a mobile app for customers.', status: 'active', ownerId: 2, createdAt: $now),
            new Project(name: 'Data Migration', description: 'Migrate legacy data to new system.', status: 'completed', ownerId: 1, createdAt: $now),
        ];

        foreach ($projects as $p) {
            $repo->save($p);
        }
    }
}
