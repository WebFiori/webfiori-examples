<?php
namespace Tests;

use App\Mail\Mailer;
use App\Mail\NullTransport;
use App\Mail\TestAccountFactory;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase {

    private NullTransport $transport;
    private Mailer $mailer;

    protected function setUp(): void {
        $this->transport = new NullTransport();
        $this->mailer = new Mailer(TestAccountFactory::create(), $this->transport);
    }

    // --- Welcome email ---

    public function testWelcomeEmailSent(): void {
        $this->mailer->sendWelcome('user@example.com', 'Alice', 'https://example.com/activate?token=abc');

        $this->assertCount(1, $this->transport->getSentMessages());
    }

    public function testWelcomeEmailSubject(): void {
        $this->mailer->sendWelcome('user@example.com', 'Alice', 'https://example.com/activate?token=abc');

        $email = $this->transport->getLastMessage();
        $this->assertEquals('Welcome to the platform', $email->getSubject());
    }

    public function testWelcomeEmailRecipient(): void {
        $this->mailer->sendWelcome('alice@example.com', 'Alice', 'https://example.com/activate');

        $email = $this->transport->getLastMessage();
        $to = $email->getTo();
        $this->assertArrayHasKey('alice@example.com', $to);
    }

    public function testWelcomeEmailBodyContainsName(): void {
        $this->mailer->sendWelcome('user@example.com', 'Alice', 'https://example.com/activate');

        $email = $this->transport->getLastMessage();
        $this->assertStringContainsString('Alice', (string) $email);
    }

    public function testWelcomeEmailBodyContainsActivationUrl(): void {
        $this->mailer->sendWelcome('user@example.com', 'Alice', 'https://example.com/activate?token=xyz');

        $email = $this->transport->getLastMessage();
        $this->assertStringContainsString('https://example.com/activate?token=xyz', (string) $email);
    }

    // --- Password reset email ---

    public function testPasswordResetEmailSent(): void {
        $this->mailer->sendPasswordReset('user@example.com', 'https://example.com/reset?token=123');

        $this->assertCount(1, $this->transport->getSentMessages());
    }

    public function testPasswordResetSubject(): void {
        $this->mailer->sendPasswordReset('user@example.com', 'https://example.com/reset');

        $email = $this->transport->getLastMessage();
        $this->assertEquals('Reset your password', $email->getSubject());
    }

    public function testPasswordResetBodyContainsUrl(): void {
        $this->mailer->sendPasswordReset('user@example.com', 'https://example.com/reset?token=abc123');

        $email = $this->transport->getLastMessage();
        $this->assertStringContainsString('https://example.com/reset?token=abc123', (string) $email);
    }

    // --- Order confirmation email ---

    public function testOrderConfirmationEmailSent(): void {
        $this->mailer->sendOrderConfirmation('user@example.com', 'Bob', 42, 199.99);

        $this->assertCount(1, $this->transport->getSentMessages());
    }

    public function testOrderConfirmationSubject(): void {
        $this->mailer->sendOrderConfirmation('user@example.com', 'Bob', 42, 199.99);

        $email = $this->transport->getLastMessage();
        $this->assertEquals('Order #42 confirmed', $email->getSubject());
    }

    public function testOrderConfirmationBodyContainsTotal(): void {
        $this->mailer->sendOrderConfirmation('user@example.com', 'Bob', 42, 199.99);

        $email = $this->transport->getLastMessage();
        $this->assertStringContainsString('199.99', (string) $email);
    }

    public function testOrderConfirmationBodyContainsOrderId(): void {
        $this->mailer->sendOrderConfirmation('user@example.com', 'Bob', 42, 199.99);

        $email = $this->transport->getLastMessage();
        $this->assertStringContainsString('#42', (string) $email);
    }

    // --- NullTransport ---

    public function testNullTransportCapturesMultipleEmails(): void {
        $this->mailer->sendWelcome('a@example.com', 'A', 'https://example.com/activate');
        $this->mailer->sendPasswordReset('b@example.com', 'https://example.com/reset');
        $this->mailer->sendOrderConfirmation('c@example.com', 'C', 1, 50.00);

        $this->assertCount(3, $this->transport->getSentMessages());
    }

    public function testNullTransportReset(): void {
        $this->mailer->sendWelcome('user@example.com', 'User', 'https://example.com/activate');
        $this->assertCount(1, $this->transport->getSentMessages());

        $this->transport->reset();
        $this->assertCount(0, $this->transport->getSentMessages());
    }

    // --- SendMode ---

    public function testSendModeStoreWritesFile(): void {
        $storePath = sys_get_temp_dir() . '/webfiori-email-test-' . uniqid();
        mkdir($storePath);

        $email = new \WebFiori\Mail\Email(TestAccountFactory::create());
        $email->setSubject('Test Store Mode');
        $email->addTo('user@example.com');
        $email->insert('p')->text('Hello from store mode.');
        $email->setMode(\WebFiori\Mail\SendMode::TEST_STORE, ['store-path' => $storePath]);
        $email->send();

        $files = glob($storePath . '/*/*.html');
        $this->assertNotEmpty($files);

        // Cleanup
        foreach ($files as $f) {
            unlink($f);
        }

        foreach (glob($storePath . '/*') as $dir) {
            rmdir($dir);
        }
        rmdir($storePath);
    }
}
