<?php
namespace App\Events;

use App\Domain\Subscription;

class SubscriptionExpiredEvent {
    public function __construct(public readonly Subscription $subscription) {
    }
}
