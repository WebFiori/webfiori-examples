<?php
namespace App\Apis;

use App\Domain\Invoice;
use App\Events\InvoiceCreatedEvent;
use App\Infrastructure\Repository\InvoiceRepository;
use WebFiori\Database\Database;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Framework\Access;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\PreAuthorize;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\RequiresAuth;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\ForbiddenException;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\SecurityContext;
use WebFiori\Http\WebService;

#[RestController('invoices', 'Invoice management API')]
#[RequiresAuth]
class InvoiceService extends WebService {
    public function isAuthorized(): bool {
        return SecurityContext::isAuthenticated();
    }

    /**
     * List invoices. Tenant users see only their tenant's invoices.
     * Super-admins see all.
     */
    #[GetMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAuthority('invoices.view')")]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true)]
    public function getInvoices(?int $id = null): array {
        $db = new Database(App::getConfig()->getDBConnection('billing'));
        $repo = new InvoiceRepository($db);
        $user = SecurityContext::getCurrentUser();

        if ($id !== null) {
            $invoice = $repo->findById($id);

            if ($invoice === null) {
                throw new NotFoundException('Invoice not found.');
            }

            if (!Access::can($user, 'invoices.view', $invoice)) {
                throw new ForbiddenException('Access denied.');
            }

            return [$invoice];
        }

        // Tenant isolation: non-super-admins see only their tenant
        if (in_array('super-admin', $user->getRoles())) {
            return $repo->findAll();
        }

        return $repo->findByTenantId($user->tenantId);
    }

    /**
     * Generate an invoice for a tenant. Super-admin only.
     */
    #[PostMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAuthority('invoices.generate')")]
    #[RequestParam(name: 'tenantId', type: ParamType::INT)]
    #[RequestParam(name: 'amount', type: ParamType::DOUBLE)]
    #[RequestParam(name: 'period', type: ParamType::STRING, optional: true)]
    public function generateInvoice(?int $tenantId = null, ?float $amount = null, ?string $period = null): array {
        $db = new Database(App::getConfig()->getDBConnection('billing'));
        $repo = new InvoiceRepository($db);

        $invoice = new Invoice(
            tenantId: $tenantId,
            amount: $amount,
            status: Invoice::STATUS_PENDING,
            period: $period,
            createdAt: date('Y-m-d H:i:s')
        );

        $repo->save($invoice);

        // Get created ID
        $invoices = $repo->findByTenantId($tenantId);
        $invoice->id = !empty($invoices) ? end($invoices)->id : null;

        EventDispatcherFacade::dispatch(new InvoiceCreatedEvent($invoice));

        return [$invoice];
    }
}
