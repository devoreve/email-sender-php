<?php

namespace App\Mailer\Factory;

use App\Mailer\ValueObject\EmailConfig;
use Swift_Mailer;
use Swift_SmtpTransport;

class SwiftMailerFactory
{
    public static function create(EmailConfig $config): Swift_Mailer
    {
        $transport = (new Swift_SmtpTransport($config->getHost(), $config->getPort()))
            ->setUsername($config->getUsername())
            ->setPassword($config->getPassword());

        return new Swift_Mailer($transport);
    }
}