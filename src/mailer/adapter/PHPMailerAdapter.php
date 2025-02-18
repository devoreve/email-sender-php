<?php

namespace App\Mailer\adapter;

use App\Mailer\MailerException;
use App\Mailer\MailerInterface;
use App\Mailer\ValueObject\EmailAddress;
use App\Mailer\ValueObject\EmailConfig;
use App\Mailer\ValueObject\EmailContent;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

readonly class PHPMailerAdapter implements MailerInterface
{
    public function __construct(private PHPMailer $mailer) {}

    public function send(EmailAddress $from, EmailAddress $to, EmailContent $content): void
    {
        try {
            $this->mailer->setFrom($from->getEmail(), $from->getName());
            $this->mailer->addAddress($to->getEmail(), $to->getName());

            $this->mailer->isHTML($content->isHtml());
            $this->mailer->Subject = $content->getSubject();
            $this->mailer->Body = $content->getBody();

            $this->mailer->send();
        } catch (Exception $e) {
            throw new MailerException($e->getMessage());
        }
    }
}