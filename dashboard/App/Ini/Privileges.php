<?php
namespace App\Ini;

use WebFiori\Framework\Access;
use WebFiori\Framework\User;

class Privileges {
    public static function initialize() {
        // Top-level: Admin gets everything
        Access::newGroup('SYSTEM_ADMIN');
        Access::newPrivileges('SYSTEM_ADMIN', ['MANAGE_USERS', 'MANAGE_SETTINGS', 'VIEW_AUDIT_LOG']);

        // Child of admin: Manager gets project + reporting + base
        Access::newGroup('PROJECT_MANAGEMENT', 'SYSTEM_ADMIN');
        Access::newPrivileges('PROJECT_MANAGEMENT', ['CREATE_PROJECT', 'EDIT_PROJECT', 'DELETE_PROJECT']);

        // Child of manager: Reporting
        Access::newGroup('REPORTING', 'PROJECT_MANAGEMENT');
        Access::newPrivileges('REPORTING', ['VIEW_REPORTS', 'GENERATE_REPORTS']);

        // Child of reporting: Base viewer
        Access::newGroup('BASE', 'REPORTING');
        Access::newPrivilege('BASE', 'VIEW_PROJECTS');
    }

    /**
     * Returns the group ID to assign for a given role.
     * Assigning a parent group gives all child privileges too.
     */
    public static function groupForRole(string $role): string {
        return match ($role) {
            'admin' => 'SYSTEM_ADMIN',
            'manager' => 'PROJECT_MANAGEMENT',
            default => 'BASE',
        };
    }

    /**
     * Returns all privilege IDs for a role using User::addToGroup().
     *
     * @return string[]
     */
    public static function privilegesForRole(string $role): array {
        $user = new User();
        $user->addToGroup(self::groupForRole($role));

        return array_map(fn ($p) => $p->getID(), $user->privileges());
    }
}
