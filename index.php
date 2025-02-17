<?php

use App\Mailer\MailerService;

require 'vendor/autoload.php';

// Récupération de la configuration
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$config = require 'config.php';

// Informations de l'email
$mailFrom = ['cedric@prof.dev', 'Cédric Prof'];
$mailTo = ['cda@3wa.dev', 'CDA 33'];
$subject = 'Envoi de mail';
$body = 'Ceci est un email au <strong>format HTML</strong> !';

// Envoi de l'email grâce au service
$mailerService = new MailerService($config);
$mailerService->send($mailFrom, $mailTo, $subject, $body);