<?php
namespace App\Pages;

use App\Infrastructure\Repository\ProjectRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Ui\HTMLNode;

class ProjectDetailPage extends BasePage {
    public function __construct() {
        parent::__construct();
        $id = $this->getParameterValue('id');
        $db = new Database(App::getConfig()->getDBConnection('dashboard'));
        $project = (new ProjectRepository($db))->findByIdWithOwner((int) $id);

        if ($project === null) {
            App::getResponse()->setCode(404);
            $this->insert('p')->text($this->get('common/not-found'));

            return;
        }

        $this->setTitle($project->name);
        $this->insert(new HTMLNode('h1'))->text($project->name);
        $this->insert(new HTMLNode('p'))->text($this->get('common/status').': '.$project->status);
        $this->insert(new HTMLNode('p'))->text($this->get('common/owner').': '.($project->ownerName ?? 'N/A'));
        $this->insert(new HTMLNode('p'))->text($project->description);
    }
}
