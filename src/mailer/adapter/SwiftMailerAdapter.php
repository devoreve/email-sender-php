<?php

namespace App\Mailer\adapter;

use App\Mailer\MailerInterface;
use App\Mailer\ValueObject\EmailAddress;
use App\Mailer\ValueObject\EmailConfig;
use App\Mailer\ValueObject\EmailContent;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

readonly class SwiftMailerAdapter implements MailerInterface
{
    private Swift_Mailer $mailer;

    public function __construct(EmailConfig $config)
    {
        $transport = (new Swift_SmtpTransport($config->getHost(), $config->getPort()))
            ->setUsername($config->getUsername())
            ->setPassword($config->getPassword());

        $this->mailer = new Swift_Mailer($transport);
    }

    public function send(EmailAddress $from, EmailAddress $to, EmailContent $content): void
    {
        $message = (new Swift_Message($content->getSubject()))
            ->setFrom([$from->getEmail() => $from->getName()])
            ->setTo([$to->getEmail() => $to->getName()])
            ->setBody($content->getBody(), 'text/html');

        $this->mailer->send($message);
    }
}