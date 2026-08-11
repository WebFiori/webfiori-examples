<?php
namespace App\Mail;

use WebFiori\Mail\AccountOption;
use WebFiori\Mail\Email;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Mail\TransportInterface;

/**
 * Sends transactional emails for the application.
 *
 * All email composition lives here. Controllers and services call these
 * methods and never touch Email or SMTPAccount directly.
 */
class Mailer {
    private TransportInterface $transport;

    public function __construct(
        private SMTPAccount $account,
        ?TransportInterface $transport = null
    ) {
        $this->transport = $transport ?? new \WebFiori\Mail\SmtpTransport();
    }

    /**
     * Sends a welcome email to a newly registered user.
     */
    public function sendWelcome(string $toAddress, string $toName, string $activationUrl): void {
        $email = new Email($this->account);
        $email->setSubject('Welcome to the platform');
        $email->addTo($toAddress, $toName);

        // $email->insert() accepts a tag name string and appends to body
        $container = $email->insert('div');
        $container->setAttribute('style', 'font-family: Arial, sans-serif; max-width: 600px;');

        // For child nodes, use addChild() on HTMLNode
        $container->addChild('h2')->text("Hello $toName,");
        $container->addChild('p')->text('Thanks for signing up. Click the button below to activate your account.');

        $btn = $container->addChild('a');
        $btn->setAttribute('href', $activationUrl);
        $btn->setAttribute('style', 'display:inline-block; padding:10px 20px; background:#2563eb; color:white; text-decoration:none; border-radius:4px;');
        $btn->text('Activate Account');

        $container->addChild('p')
            ->setAttribute('style', 'color:#6b7280; font-size:12px;')
            ->text('If you did not sign up, ignore this email.');

        $email->send($this->transport);
    }

    /**
     * Sends a password reset email.
     */
    public function sendPasswordReset(string $toAddress, string $resetUrl): void {
        $email = new Email($this->account);
        $email->setSubject('Reset your password');
        $email->addTo($toAddress);

        $container = $email->insert('div');
        $container->setAttribute('style', 'font-family: Arial, sans-serif; max-width: 600px;');

        $container->addChild('p')->text('We received a request to reset your password.');

        $btn = $container->addChild('a');
        $btn->setAttribute('href', $resetUrl);
        $btn->setAttribute('style', 'display:inline-block; padding:10px 20px; background:#dc2626; color:white; text-decoration:none; border-radius:4px;');
        $btn->text('Reset Password');

        $container->addChild('p')
            ->setAttribute('style', 'color:#6b7280; font-size:12px;')
            ->text('This link expires in 1 hour. If you did not request a reset, ignore this email.');

        $email->send($this->transport);
    }

    /**
     * Sends an order confirmation email.
     */
    public function sendOrderConfirmation(string $toAddress, string $toName, int $orderId, float $total): void {
        $email = new Email($this->account);
        $email->setSubject("Order #$orderId confirmed");
        $email->addTo($toAddress, $toName);

        // before-send callback: adds a timestamp line just before the email is sent
        $email->addBeforeSend(function (Email $e) use ($orderId) {
            $e->insert('p')
              ->setAttribute('style', 'color:#6b7280; font-size:11px;')
              ->text('Sent at: ' . date('Y-m-d H:i:s') . " | Ref: #$orderId");
        });

        $container = $email->insert('div');
        $container->setAttribute('style', 'font-family: Arial, sans-serif; max-width: 600px;');
        $container->addChild('h2')->text("Order Confirmed");
        $container->addChild('p')->text("Hi $toName, your order #$orderId has been placed.");

        $table = $container->addChild('table');
        $table->setAttribute('style', 'width:100%; border-collapse:collapse;');

        $row = $table->addChild('tr');
        $row->addChild('td')->setAttribute('style', 'padding:8px; border:1px solid #e5e7eb;')->text('Order ID');
        $row->addChild('td')->setAttribute('style', 'padding:8px; border:1px solid #e5e7eb;')->text("#$orderId");

        $row2 = $table->addChild('tr');
        $row2->addChild('td')->setAttribute('style', 'padding:8px; border:1px solid #e5e7eb;')->text('Total');
        $row2->addChild('td')->setAttribute('style', 'padding:8px; border:1px solid #e5e7eb;')->text('$' . number_format($total, 2));

        $email->send($this->transport);
    }
}
