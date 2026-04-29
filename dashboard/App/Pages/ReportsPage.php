<?php
namespace App\Pages;

use App\Infrastructure\Repository\ReportRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Ui\HTMLNode;

class ReportsPage extends BasePage {
    public function __construct() {
        parent::__construct();
        $this->setTitle($this->get('nav/reports'));

        $this->insert(new HTMLNode('h1'))->text($this->get('nav/reports'));

        $privileges = SessionsManager::get('user-privileges') ?? [];

        if (in_array('GENERATE_REPORTS', $privileges)) {
            $btn = new HTMLNode('button', ['id' => 'gen-report', 'data-base-url' => App::getConfig()->getBaseURL()]);
            $btn->text($this->get('common/create').' '.$this->get('nav/reports'));
            $this->insert($btn);
            $this->insert(new HTMLNode('p', ['id' => 'gen-status', 'style' => 'display:none;color:green']));
            $this->addJS(App::getConfig()->getBaseURL().'/assets/js/reports.js', ['defer' => '']);
        }

        $db = new Database(App::getConfig()->getDBConnection('dashboard'));
        $reports = (new ReportRepository($db))->findAllWithUser();

        $rows = [];

        foreach ($reports as $r) {
            $rows[] = [$r->title, $r->generatedByName ?? '', $r->createdAt ?? ''];
        }

        $this->insert(TableHelper::create(
            [$this->get('common/title'), $this->get('common/generated-by'), $this->get('common/created')],
            $rows
        ));
    }
}
