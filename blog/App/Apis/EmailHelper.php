<?php
namespace App\Apis;

use WebFiori\Framework\App;
use WebFiori\Framework\EmailMessage;
use WebFiori\Mail\Email;
use WebFiori\Mail\SendMode;
use WebFiori\Mail\SMTPAccount;

/**
 * Helper to create an email message.
 *
 * Uses SMTP when configured, otherwise stores as HTML in Storage/Logs/emails/.
 */
class EmailHelper {
    public static function create(): Email {
        $smtp = App::getConfig()->getSMTPConnection('no-reply');

        if ($smtp instanceof SMTPAccount) {
            return new EmailMessage('no-reply');
        }

        $message = new Email(new SMTPAccount());
        $storePath = APP_PATH . 'Storage' . DS . 'Logs' . DS . 'emails';

        if (!is_dir($storePath)) {
            mkdir($storePath, 0755, true);
        }
        $message->setMode(SendMode::TEST_STORE, ['store-path' => $storePath]);

        return $message;
    }
}
