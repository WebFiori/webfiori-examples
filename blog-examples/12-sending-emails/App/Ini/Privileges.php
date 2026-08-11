<?php
namespace App\Ini;

use App\Mail\Mailer;
use App\Mail\NullTransport;
use App\Mail\TestAccountFactory;
use WebFiori\Container\ContainerFacade;
use WebFiori\Mail\AccountOption;
use WebFiori\Mail\SMTPAccount;

class Privileges {
    public static function initialize(): void {
        if (getenv('APP_ENV') === 'testing') {
            ContainerFacade::singleton(NullTransport::class, NullTransport::class);

            ContainerFacade::bind(Mailer::class, function ($container) {
                return new Mailer(
                    TestAccountFactory::create(),
                    $container->make(NullTransport::class)
                );
            });
        } else {
            ContainerFacade::bind(Mailer::class, function () {
                $account = new SMTPAccount([
                    AccountOption::SERVER_ADDRESS => getenv('SMTP_HOST'),
                    AccountOption::PORT           => (int) getenv('SMTP_PORT'),
                    AccountOption::USERNAME       => getenv('SMTP_USERNAME'),
                    AccountOption::PASSWORD       => getenv('SMTP_PASSWORD'),
                    AccountOption::SENDER_ADDRESS => getenv('SMTP_FROM'),
                    AccountOption::SENDER_NAME    => getenv('SMTP_FROM_NAME'),
                    AccountOption::NAME           => 'no-reply',
                ]);

                return new Mailer($account);
            });
        }
    }
}
