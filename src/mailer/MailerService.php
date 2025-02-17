<?php

namespace App\Mailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService
{
    private PHPMailer $mailer;

    public function __construct(array $config)
    {
        $this->mailer = new PHPMailer(true);

        $this->mailer->isSMTP();
        $this->mailer->Host = $config['SMTP_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Port = $config['SMTP_PORT'];
        $this->mailer->Username = $config['SMTP_USERNAME'];
        $this->mailer->Password = $config['SMTP_PASSWORD'];
    }

    public function send(array $mailFrom, array $mailTo, string $subject, string $body): void
    {
        try {
            [$address, $name] = $mailFrom;
            $this->mailer->setFrom($address, $name);

            [$address, $name] = $mailTo;
            $this->mailer->addAddress($address, $name);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            $this->mailer->send();
        } catch (Exception $e) {
            echo "Une erreur est survenue : $this->mailer->ErrorInfo";
        }
    }
}