<?php
namespace App\Jobs;

use App\Domain\Invoice;
use App\Events\PaymentFailedEvent;
use App\Infrastructure\Repository\InvoiceRepository;
use App\Services\BillingProviderInterface;
use WebFiori\Container\ContainerFacade;
use WebFiori\Database\Database;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Framework\App;
use WebFiori\Log\FileLogger;
use WebFiori\Queue\Job;

/**
 * Processes payment for a pending invoice. Retries 3 times with 60s backoff.
 */
class ProcessInvoicePaymentJob implements Job {
    public function __construct(private int $invoiceId) {
    }

    public function getMaxAttempts(): int {
        return 3;
    }

    public function getRetryDelaySeconds(): int {
        return 60;
    }

    public function handle(): void {
        $logger = new FileLogger(APP_PATH . 'Storage' . DS . 'Logs', 'info');
        $db = new Database(App::getConfig()->getDBConnection('billing'));
        $repo = new InvoiceRepository($db);
        $invoice = $repo->findById($this->invoiceId);

        if ($invoice === null || $invoice->status !== Invoice::STATUS_PENDING) {
            return;
        }

        /** @var BillingProviderInterface $provider */
        $provider = ContainerFacade::make(BillingProviderInterface::class);
        $result = $provider->charge($invoice->tenantId, $invoice->amount);

        if ($result['success']) {
            $invoice->status = Invoice::STATUS_PAID;
            $repo->save($invoice);
            $logger->info('Invoice paid', ['invoice_id' => $invoice->id, 'txn' => $result['transactionId']]);
        } else {
            $invoice->status = Invoice::STATUS_FAILED;
            $repo->save($invoice);
            EventDispatcherFacade::dispatch(new PaymentFailedEvent($invoice, $result['error']));

            throw new \RuntimeException('Payment failed: ' . $result['error']);
        }
    }
}
