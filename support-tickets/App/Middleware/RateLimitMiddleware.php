<?php
namespace App\Middleware;

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\RequestMethod;
use WebFiori\Http\Response;

/**
 * Rate-limiting middleware that restricts ticket creation to prevent spam.
 *
 * Allows a maximum number of POST requests per session within a time window.
 */
class RateLimitMiddleware extends AbstractMiddleware {
    private const MAX_REQUESTS = 5;
    private const WINDOW_SECONDS = 60;

    public function __construct() {
        parent::__construct('rate-limit');
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
        if ($request->getMethod() !== RequestMethod::POST) {
            return;
        }

        SessionsManager::start('wf-session');
        $timestamps = SessionsManager::get('rate-limit-timestamps') ?? [];
        $now = time();

        // Remove expired entries
        $timestamps = array_filter($timestamps, fn ($ts) => ($now - $ts) < self::WINDOW_SECONDS);

        if (count($timestamps) >= self::MAX_REQUESTS) {
            $response->setCode(429);
            $response->addHeader('content-type', 'application/json');
            $response->write('{"message":"Too many requests. Please wait before submitting again.","type":"error","http-code":429}');
            $response->send();
        }

        $timestamps[] = $now;
        SessionsManager::set('rate-limit-timestamps', $timestamps);
    }
}
