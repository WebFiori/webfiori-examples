<?php
namespace App\Tasks;

use App\Domain\Subscription;
use App\Events\SubscriptionExpiredEvent;
use App\Infrastructure\Repository\SubscriptionRepository;
use WebFiori\Database\Database;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Framework\App;
use WebFiori\Framework\Scheduler\AbstractTask;
use WebFiori\Log\FileLogger;

/**
 * Marks expired subscriptions and dispatches events. Runs daily at 1 AM.
 */
class ExpireSubscriptionsTask extends AbstractTask {
    public function __construct() {
        parent::__construct('expire-subscriptions', '0 1 * * *', 'Expire overdue subscriptions.');
    }

    public function afterExec(): void {
    }

    public function execute(): void {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs', 'info');
        $db = new Database(App::getConfig()->getDBConnection('billing'));
        $repo = new SubscriptionRepository($db);

        $expired = $repo->findExpired();

        foreach ($expired as $sub) {
            $sub->status = Subscription::STATUS_EXPIRED;
            $repo->save($sub);
            EventDispatcherFacade::dispatch(new SubscriptionExpiredEvent($sub));
        }

        $logger->info('Subscriptions expired', ['count' => count($expired)]);
    }

    public function onFail(): void {
    }

    public function onSuccess(): void {
    }
}
