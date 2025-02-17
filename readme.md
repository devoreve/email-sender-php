# Design patterns et bonnes pratiques

## Étude de cas : envoi d'email

### Installation

Si vous souhaitez tester le code chez vous, il vous faudra un serveur PHP et l'outil **composer** installé.
Une fois que c'est le cas, suivez les étapes suivantes pour installer le projet :
1. Clonez le dépôt dans un dossier
2. Déplacez-vous dans le dossier via le terminal
3. Lancez la commande *composer install*
4. Copiez le fichier **.env.example** et renommez cette copie en **.env**
5. Mettez à jour le fichier **.env** avec vos propres informations (vous pouvez utiliser un service tel que **Mailtrap** si vous n'avez pas de serveur SMTP)

### Contexte

Nous avons créé un programme qui permet d'envoyer un email grâce à une bibliothèque externe. 
Nous avons développé un service **MailerService** qui s'occupe d'initialiser la bibliothèque avec la configuration fournie
et d'envoyer le mail.

```php
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
```

Et le point d'entrée de notre application appelle le service pour envoyer le mail.

```php
// Récupération de la configuration
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$config = require 'config.php';

// Informations de l'email
$mailFrom = ['cedric@prof.dev', 'Cédric Prof'];
$mailTo = ['cda@3wa.dev', 'CDA'];
$subject = 'Envoi de mail';
$body = 'Ceci est un email au <strong>format HTML</strong> !';

// Envoi de l'email grâce au service
$mailerService = new MailerService($config);
$mailerService->send($mailFrom, $mailTo, $subject, $body);
```

### Analyse

Cela fonctionne bien en l'état mais plusieurs problèmes peuvent se poser :
* Nous utilisons actuellement beaucoup les tableaux pour stocker des données (pour l'adresse et le nom du destinataire par exemple) mais ces dernières ne sont pas contrôlées => Que se passe t-il si je rentre une email invalide par exemple ?
* La classe PHPMailer est instanciée directement dans le service => Que se passe t-il si l'on veut changer de bibliothèque d'envoi de mail ?