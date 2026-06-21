<?php
namespace App\Apis;

use App\Domain\Appointment;
use App\Events\AppointmentBookedEvent;
use App\Events\AppointmentCancelledEvent;
use App\Infrastructure\Repository\AppointmentRepository;
use App\Infrastructure\Repository\ServiceRepository;
use WebFiori\Database\Database;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Framework\Access;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\DeleteMapping;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\PreAuthorize;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\RequiresAuth;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\BadRequestException;
use WebFiori\Http\Exceptions\ForbiddenException;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\SecurityContext;
use WebFiori\Http\WebService;

#[RestController('appointments', 'Appointment management API')]
#[RequiresAuth]
class AppointmentService extends WebService {
    public function isAuthorized(): bool {
        return SecurityContext::isAuthenticated();
    }

    #[GetMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAuthority('appointments.view')")]
    #[RequestParam(name: 'id', type: ParamType::INT, optional: true)]
    public function getAppointments(?int $id = null): array {
        $db = $this->getDb();
        $repo = new AppointmentRepository($db);
        $user = SecurityContext::getCurrentUser();

        if ($id !== null) {
            $appt = $repo->findById($id);

            if ($appt === null) {
                throw new NotFoundException('Appointment not found.');
            }

            if (!Access::can($user, 'appointments.view', $appt)) {
                throw new ForbiddenException('You cannot view this appointment.');
            }

            return [$appt];
        }

        $role = $user->getRoles()[0] ?? '';

        if ($role === 'admin') {
            return $repo->findAll();
        }

        if ($role === 'provider') {
            return $repo->findByProviderId($user->getId());
        }

        return $repo->findByPatientId($user->getId());
    }

    #[PostMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAuthority('appointments.create')")]
    #[RequestParam(name: 'providerId', type: ParamType::INT)]
    #[RequestParam(name: 'serviceId', type: ParamType::INT)]
    #[RequestParam(name: 'startTime', type: ParamType::STRING, description: 'Y-m-d H:i:s')]
    #[RequestParam(name: 'notes', type: ParamType::STRING, optional: true, default: '')]
    public function bookAppointment(?int $providerId = null, ?int $serviceId = null, ?string $startTime = null, ?string $notes = null): array {
        $db = $this->getDb();
        $user = SecurityContext::getCurrentUser();

        $service = (new ServiceRepository($db))->findById($serviceId);

        if ($service === null) {
            throw new NotFoundException('Service not found.');
        }

        $start = strtotime($startTime);

        if ($start === false || $start <= time()) {
            throw new BadRequestException('Start time must be a valid future datetime.');
        }

        $endTime = date('Y-m-d H:i:s', $start + ($service->durationMinutes * 60));

        $appt = new Appointment(
            patientId: $user->getId(),
            providerId: $providerId,
            serviceId: $serviceId,
            startTime: $startTime,
            endTime: $endTime,
            status: Appointment::STATUS_BOOKED,
            notes: $notes ?: null,
            createdAt: date('Y-m-d H:i:s')
        );

        $repo = new AppointmentRepository($db);
        $repo->save($appt);

        // Get created ID
        $result = $db->table('appointments')->select()
            ->where('patient-id', $user->getId())
            ->execute();
        $rows = $result->fetchAll();
        $appt->id = !empty($rows) ? (int) end($rows)['id'] : null;

        EventDispatcherFacade::dispatch(new AppointmentBookedEvent($appt));

        return [$appt];
    }

    #[DeleteMapping]
    #[ResponseBody]
    #[PreAuthorize("hasAuthority('appointments.cancel')")]
    #[RequestParam(name: 'id', type: ParamType::INT)]
    public function cancelAppointment(?int $id = null): array {
        $db = $this->getDb();
        $repo = new AppointmentRepository($db);
        $appt = $repo->findById($id);

        if ($appt === null) {
            throw new NotFoundException('Appointment not found.');
        }

        $user = SecurityContext::getCurrentUser();

        if (!Access::can($user, 'appointments.cancel', $appt)) {
            throw new ForbiddenException('You cannot cancel this appointment.');
        }

        $appt->status = Appointment::STATUS_CANCELLED;
        $repo->save($appt);

        EventDispatcherFacade::dispatch(new AppointmentCancelledEvent($appt));

        return [$appt];
    }

    private function getDb(): Database {
        return new Database(App::getConfig()->getDBConnection('booking'));
    }
}
