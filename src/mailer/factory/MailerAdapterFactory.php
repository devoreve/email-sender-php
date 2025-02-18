<?php

namespace App\Mailer\Factory;

use App\Mailer\adapter\PHPMailerAdapter;
use App\Mailer\adapter\SwiftMailerAdapter;
use App\Mailer\MailerInterface;
use App\Mailer\ValueObject\EmailConfig;

enum MailerType
{
    case PHPMailer;
    case SwiftMailer;
}

class MailerAdapterFactory
{
    public static function create(EmailConfig $config, MailerType $mailerType): MailerInterface
    {
        return match ($mailerType) {
            MailerType::PHPMailer => new PHPMailerAdapter(PHPMailerFactory::create($config)),
            MailerType::SwiftMailer => new SwiftMailerAdapter(SwiftMailerFactory::create($config)),
            default => throw new \InvalidArgumentException("Type de mailer inconnu : $mailerType->name")
        };
    }
}