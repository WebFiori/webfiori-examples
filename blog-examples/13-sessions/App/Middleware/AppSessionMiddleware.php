<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\StartSessionMiddleware;
use WebFiori\Framework\Session\SessionOption;

/**
 * Custom session middleware with application-specific configuration.
 * 
 * Extends StartSessionMiddleware to set:
 * - Custom session name ('myapp')
 * - 30-minute duration
 * - Refresh timeout on each request
 */
class AppSessionMiddleware extends StartSessionMiddleware {
    public function __construct() {
        parent::__construct();
        
        $this->setSessionName('myapp');
        $this->setSessionOptions([
            SessionOption::DURATION => 30,    // 30 minutes
            SessionOption::REFRESH  => true,  // Reset timeout on each request
        ]);
    }
}
