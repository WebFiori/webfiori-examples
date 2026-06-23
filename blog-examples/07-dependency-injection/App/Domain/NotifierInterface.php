<?php
namespace App\Domain;

/**
 * Contract for sending notifications.
 */
interface NotifierInterface {
    public function send(string $message): void;
}
