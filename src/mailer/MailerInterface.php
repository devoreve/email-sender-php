<?php

namespace App\Mailer;

use App\Mailer\ValueObject\EmailAddress;
use App\Mailer\ValueObject\EmailContent;

interface MailerInterface
{
    public function send(EmailAddress $from, EmailAddress $to, EmailContent $content): void;
}