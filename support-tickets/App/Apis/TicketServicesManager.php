<?php
namespace App\Apis;

use WebFiori\Http\WebServicesManager;

/**
 * Registers all support ticket API services.
 */
class TicketServicesManager extends WebServicesManager {
    public function __construct() {
        parent::__construct();
        $this->addService(new TicketService());
        $this->addService(new ReplyService());
        $this->addService(new AttachmentService());
    }
}
