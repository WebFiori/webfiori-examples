<?php

namespace App\Ini;

use App\Health\DatabaseCheck;
use App\Health\PaymentGatewayCheck;
use WebFiori\Framework\Health\Checks\CacheCheck;
use WebFiori\Framework\Health\Checks\StorageCheck;
use WebFiori\Framework\Health\HealthCheck;
use WebFiori\Log\FileLogger;
use WebFiori\Log\LoggerFacade;
use WebFiori\Log\LogLevel;

class AutoLoad {
    public static function initialize() {
        // Configure logging
        $logDir = dirname(__DIR__) . '/Storage/Logs';
        LoggerFacade::setInstance(new FileLogger($logDir, LogLevel::DEBUG));

        // Register health checks
        HealthCheck::register(new DatabaseCheck());
        HealthCheck::register(new PaymentGatewayCheck());
        HealthCheck::register(new StorageCheck());
        HealthCheck::register(new CacheCheck());

        // Log health check failures
        HealthCheck::afterAll(function (array $result) {
            if ($result['status'] === 'fail') {
                LoggerFacade::critical('Health checks failing', $result);
            }
        });
    }
}
