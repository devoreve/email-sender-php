<?php

use App\Mailer\adapter\PHPMailerAdapter;
use App\Mailer\adapter\SwiftMailerAdapter;
use App\Mailer\MailerService;
use App\Mailer\ValueObject\EmailConfig;
use App\Mailer\ValueObject\EmailAddress;
use App\Mailer\ValueObject\EmailContent;

require 'vendor/autoload.php';

try {
    // Récupération de la configuration
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $config = require 'config.php';
    $config = new EmailConfig($config['host'], $config['port'], $config['username'], $config['password']);

    // Informations de l'email
    $mailFrom = new EmailAddress('cedric@prof.dev', 'Cédric Prof');
    $mailTo = new EmailAddress('cda@3wa.dev', 'CDA 33');
    $mailContent = new EmailContent(
        'Envoi de mail',
        'Ceci est un email au <strong>format HTML</strong> !'
    );

    // Récupération de l'adapter
    // On peut utiliser PHPMailer
    $mailer = new PHPMailerAdapter($config);

    // Ou utiliser SwiftMailer
    $mailer = new SwiftMailerAdapter($config);

    // Envoi de l'email grâce au service dans lequel on passe l'adapter en paramètre
    $mailerService = new MailerService($mailer);
    $mailerService->send($mailFrom, $mailTo, $mailContent);
} catch (Exception $e) {
    echo "Une erreur est survenue : {$e->getMessage()}";
}

