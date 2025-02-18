<?php

namespace App\Mailer;

use App\Mailer\ValueObject\EmailAddress;
use App\Mailer\ValueObject\EmailContent;

readonly class MailerService
{
    public function __construct(private MailerInterface $mailer) {}

    public function send(EmailAddress $mailFrom, EmailAddress $mailTo, EmailContent $emailContent): void
    {
        // Notre service ici appelle la méthode send de MailerInterface
        // Il ne sait pas s'il s'agit de PhpMailer ou de SwiftMailer et il n'a pas besoin de le savoir
        $this->mailer->send($mailFrom, $mailTo, $emailContent);
    }
}