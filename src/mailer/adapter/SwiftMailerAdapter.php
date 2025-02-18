<?php

namespace App\Mailer\adapter;

use App\Mailer\MailerInterface;
use App\Mailer\ValueObject\EmailAddress;
use App\Mailer\ValueObject\EmailContent;
use Swift_Mailer;
use Swift_Message;

readonly class SwiftMailerAdapter implements MailerInterface
{
    public function __construct(private Swift_Mailer $mailer) {}

    public function send(EmailAddress $from, EmailAddress $to, EmailContent $content): void
    {
        $message = (new Swift_Message($content->getSubject()))
            ->setFrom([$from->getEmail() => $from->getName()])
            ->setTo([$to->getEmail() => $to->getName()])
            ->setBody($content->getBody(), 'text/html');

        $this->mailer->send($message);
    }
}