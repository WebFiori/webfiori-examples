<?php
namespace App\Mail;

use WebFiori\Mail\AccountOption;
use WebFiori\Mail\SMTPAccount;

class TestAccountFactory {
    public static function create(): SMTPAccount {
        return new SMTPAccount([
            AccountOption::SERVER_ADDRESS => 'smtp.example.com',
            AccountOption::PORT           => 465,
            AccountOption::USERNAME       => 'no-reply@example.com',
            AccountOption::PASSWORD       => 'secret',
            AccountOption::SENDER_ADDRESS => 'no-reply@example.com',
            AccountOption::SENDER_NAME    => 'Test App',
            AccountOption::NAME           => 'no-reply',
        ]);
    }
}
