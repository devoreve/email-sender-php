<?php

namespace App\Mailer;

use App\Mailer\ValueObject\EmailAddress;
use App\Mailer\ValueObject\EmailConfig;
use App\Mailer\ValueObject\EmailContent;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService
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

    public function send(EmailAddress $mailFrom, EmailAddress $mailTo, EmailContent $emailContent): void
    {
        try {
            $this->mailer->setFrom($mailFrom->getEmail(), $mailFrom->getName());
            $this->mailer->addAddress($mailTo->getEmail(), $mailTo->getName());

            $this->mailer->isHTML($emailContent->isHtml());
            $this->mailer->Subject = $emailContent->getSubject();
            $this->mailer->Body = $emailContent->getBody();

            $this->mailer->send();
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }
}