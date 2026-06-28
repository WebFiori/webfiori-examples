<?php
namespace App\Listeners;

use App\Events\UserRegisteredEvent;
use WebFiori\Log\LoggerFacade;

class SendWelcomeEmailListener {
    public function handle(UserRegisteredEvent $event): void {
        LoggerFacade::info('Welcome email queued', [
            'user_id' => $event->userId,
            'email' => $event->email,
        ]);
    }
}
