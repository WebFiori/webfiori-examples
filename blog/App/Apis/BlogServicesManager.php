<?php
namespace App\Apis;

use WebFiori\Http\WebServicesManager;

/**
 * Registers all blog API services.
 */
class BlogServicesManager extends WebServicesManager {
    public function __construct() {
        parent::__construct();
        $this->addService(new AuthService());
        $this->addService(new PostService());
        $this->addService(new CategoryService());
        $this->addService(new CommentService());
    }
}
