<?php
namespace App\Jobs;

use App\Infrastructure\Repository\OrderRepository;
use App\Infrastructure\Repository\UserRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Log\FileLogger;
use WebFiori\Mail\Email;
use WebFiori\Mail\SendMode;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Queue\Job;

/**
 * Job that sends an order confirmation email after successful payment.
 */
class SendOrderConfirmationJob implements Job {
    public function __construct(private int $orderId) {
    }

    public function getMaxAttempts(): int {
        return 2;
    }

    public function getRetryDelaySeconds(): int {
        return 60;
    }

    public function handle(): void {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs', 'info');
        $db = new Database(App::getConfig()->getDBConnection('orders'));

        $order = (new OrderRepository($db))->findById($this->orderId);

        if ($order === null) {
            return;
        }

        $user = (new UserRepository($db))->findById($order->userId);

        if ($user === null) {
            return;
        }

        $email = new Email(new SMTPAccount());
        $storePath = APP_PATH . 'Storage' . DS . 'Logs' . DS . 'emails';

        if (!is_dir($storePath)) {
            mkdir($storePath, 0755, true);
        }

        $email->setMode(SendMode::TEST_STORE, ['store-path' => $storePath]);
        $email->setSubject('Order #' . $order->id . ' Confirmed');
        $email->addTo($user->email, $user->name);
        $email->insert('h2')->text('Order Confirmed');
        $email->insert('p')->text('Your order #' . $order->id . ' has been paid successfully.');
        $email->insert('p')->text('Total: $' . number_format($order->total, 2));
        $email->send();

        $logger->info('Confirmation email sent', ['order_id' => $this->orderId, 'email' => $user->email]);
    }
}
