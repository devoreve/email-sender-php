<?php

namespace App\Mailer\adapter;

use App\Mailer\MailerInterface;
use App\Mailer\ValueObject\EmailAddress;
use App\Mailer\ValueObject\EmailConfig;
use App\Mailer\ValueObject\EmailContent;
use PHPMailer\PHPMailer\PHPMailer;

readonly class PHPMailerAdapter implements MailerInterface
{
    private PHPMailer $mailer;
    public function __construct(EmailConfig $config)
    {
        $this->mailer = new PHPMailer(true);

        $this->mailer->isSMTP();
        $this->mailer->Host = $config->getHost();
        $this->mailer->SMTPAuth = true;
        $this->mailer->Port = $config->getPort();
        $this->mailer->Username = $config->getUsername();
        $this->mailer->Password = $config->getPassword();
    }

    public function send(EmailAddress $from, EmailAddress $to, EmailContent $content): void
    {
        $this->mailer->setFrom($from->getEmail(), $from->getName());
        $this->mailer->addAddress($to->getEmail(), $to->getName());

        $this->mailer->isHTML($content->isHtml());
        $this->mailer->Subject = $content->getSubject();
        $this->mailer->Body = $content->getBody();

        $this->mailer->send();
    }
}