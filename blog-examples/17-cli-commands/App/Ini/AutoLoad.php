<?php

namespace App\Ini;

use WebFiori\Framework\Health\HealthCheck;
use WebFiori\Log\FileLogger;
use WebFiori\Log\LoggerFacade;
use WebFiori\Log\LogLevel;

class AutoLoad {
    public static function initialize() {
        // Configure logging
        $logDir = dirname(__DIR__) . '/Storage/Logs';
        LoggerFacade::setInstance(new FileLogger($logDir, LogLevel::DEBUG));

        // Log health check failures
        HealthCheck::afterAll(function (array $result) {
            if ($result['status'] === 'fail') {
                LoggerFacade::critical('Health checks failing', $result);
            }
        });
    }
}
