<?php
namespace App\Pages\Admin;

use App\Infrastructure\Repository\AuditLogRepository;
use App\Pages\BasePage;
use App\Pages\TableHelper;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Ui\HTMLNode;

class AuditLogPage extends BasePage {
    public function __construct() {
        parent::__construct();
        $this->setTitle($this->get('nav/audit-log'));
        $this->insert(new HTMLNode('h1'))->text($this->get('nav/audit-log'));

        $db = new Database(App::getConfig()->getDBConnection('dashboard'));
        $entries = (new AuditLogRepository($db))->findFiltered();

        $rows = [];

        foreach ($entries as $e) {
            $rows[] = [$e->userName ?? 'System', $e->action, $e->entityType.($e->entityId ? ' #'.$e->entityId : ''), $e->ipAddress, $e->createdAt ?? ''];
        }

        $this->insert(TableHelper::create(
            [$this->get('common/user'), $this->get('common/action'), $this->get('common/entity'), $this->get('common/ip'), $this->get('common/date')],
            $rows
        ));
    }
}
