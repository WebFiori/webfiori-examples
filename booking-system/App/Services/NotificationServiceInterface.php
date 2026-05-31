<?php
namespace App\Services;

interface NotificationServiceInterface {
    public function sendSms(string $phone, string $message): bool;
}
