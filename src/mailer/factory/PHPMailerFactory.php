<?php

namespace App\Mailer\Factory;

use App\Mailer\ValueObject\EmailConfig;
use PHPMailer\PHPMailer\PHPMailer;

class PHPMailerFactory
{
    public static function create(EmailConfig $config): PHPMailer
    {
        $mailer = new PHPMailer(true);

        $mailer->isSMTP();
        $mailer->Host = $config->getHost();
        $mailer->SMTPAuth = true;
        $mailer->Port = $config->getPort();
        $mailer->Username = $config->getUsername();
        $mailer->Password = $config->getPassword();

        return $mailer;
    }
}