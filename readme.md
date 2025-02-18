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

### Améliorations

#### ValueObject

Pour s'assurer un meilleur contrôle des données, au lieu de passer par un tableau nous utiliserons des classes directement.
Voici un exemple de la classe **EmailAddress** qui encapsule la logique liée à l'adresse email (une adresse et un nom correspondant) ainsi que les contrôles nécessaires (l'adresse email est-elle valide ?).

```php
class EmailAddress
{
    public function __construct(private string $email, private string $name)
    {
        $this->setEmail($email);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Adresse email invalide : {$email}");
        }

        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
```

Nous avons également créé la classe **EmailConfig** permettant de gérer les données de configuration du serveur SMTP et la classe **EmailContent** encapsulant les données de l'email.

**Conclusion** : Grâce à l'utilisation des classes en remplacement des tableaux, nous avons un meilleur contrôle sur les données en entrée.

#### Bonnes pratiques POO

Notre code est fonctionnel et assure un meilleur contrôle des données grâce à l'utilisation de classes dédiées. Cette structure convient parfaitement tant que nous utilisons la même bibliothèque pour l'envoi des emails.

Mais que se passerait-il si nous devions changer de bibliothèque ? Pourrions-nous facilement faire évoluer notre code ?

Dans notre service, le constructeur instancie notre bibliothèque et la configure :

```php
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
```

**Problème** : En faisant cela nous créons une forte dépendance entre notre classe **MailerService** et la bibliothèque utilisée. Si nous souhaitons utiliser une autre bibliothèque il faudra modifier ce fichier à chaque fois, ce qui va à l'encontre du principe "Ouvert/Fermé" de **SOLID**. En outre notre service s'occupe de d'instancier et de configurer la bibliothèque puis d'envoyer l'email, ce qui va là encore à l'encontre d'un principe de **SOLID** : le principe de responsabilité unique.

**Solution** : Plutôt que d'instancier la bibliothèque directement dans le constructeur, nous allons lui passer en injection de dépendance.

```php
public function __construct(private readonly PHPMailer $mailer) {}
```

Toutefois cela ne règle pas complètement notre problème, nous ne pouvons toujours utiliser que cette bibliothèque dans notre service. Le principe d'inversion de dépendance de **SOLID** nous dit qu'il faut dépendre d'abstractions et non d'implémentations concrètes (comme la classe que j'utilise dans le constructeur).
C'est pourquoi nous allons ici passer par une interface **MailerInterface** que nous injecterons à notre service.

```php
interface MailerInterface
{
    public function send(EmailAddress $from, EmailAddress $to, EmailContent $content): void
}
```

```php
public function __construct(private readonly MailerInterface $mailer) {}
```

Ainsi, notre service est totalement découplé de la bibliothèque utilisée. Il ne sait pas quelle implémentation spécifique il recevra, mais il sait qu'elle respectera le contrat défini par l’interface et disposera d’une méthode **send** pour envoyer l’email.

#### Adapter

**Problème** : Nous avons maintenant une interface **MailerInterface** qui va permettre au service de ne pas dépendre d'une classe concrète. Il ne nous reste plus qu'à utiliser cette interface avec nos bibliothèques.
Cependant il ne faut jamais modifier une bibliothèque externe (ces dernières sont mises à jour par un gestionnaire de package, toute modification serait écrasée) donc comment leur faire implémenter notre interface ?

**Solution** : Nous allons utiliser le design pattern **Adapter** qui permet de faire correspondre des bibliothèques externes à nos propres interfaces.

Nous avons donc créé 2 classes qui vont adapter nos bibliothèques externes :

```php
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
```

```php
readonly class SwiftMailerAdapter implements MailerInterface
{
    private readonly Swift_Mailer $mailer;

    public function __construct(EmailConfig $config)
    {
        $transport = (new Swift_SmtpTransport($config->getHost(), $config->getPort()))
            ->setUsername($config->getUsername())
            ->setPassword($config->getPassword());

        $this->mailer = new Swift_Mailer($transport);
    }

    public function send(EmailAddress $from, EmailAddress $to, EmailContent $content): void
    {
        $message = (new Swift_Message($content->getSubject()))
            ->setFrom([$from->getEmail() => $from->getName()])
            ->setTo([$to->getEmail() => $to->getName()])
            ->setBody($content->getBody(), 'text/html');

        $this->mailer->send($message);
    }
}
```

Ces classes encapsulent les bibliothèques externes et elles implémentent l'interface **MailerInterface**, ce qui nous permet de les utiliser dans notre **MailerService**.

```php
// Récupération de l'adapter
// On peut utiliser PHPMailer
$mailer = new PHPMailerAdapter($config);

// Ou utiliser SwiftMailer
$mailer = new SwiftMailerAdapter($config);

// Envoi de l'email grâce au service dans lequel on passe l'adapter en paramètre
$mailerService = new MailerService($mailer);
$mailerService->send($mailFrom, $mailTo, $mailContent);
```

**Conclusion** : Grâce à l'injection de dépendance et au design pattern adapter, nous avons un système flexible qui nous permet de changer facilement de bibliothèque d'envoi de mail. Si nous souhaitons rajouter une autre bibliothèque externe pour gérer l'envoi d'email, il nous suffira simplement de créer un nouvel adapter.

#### Factory

Maintenant notre code nous permet bien de changer facilement de bibliothèque de manière flexible mais il reste un léger bémol dans notre code : l'adapter s'occupe de créer l'instance du mailer et d'envoyer le mail, cassant par la même occasion le principe de responsabilité unique.
Si plus tard nous devons modifier la configuration du mailer, nous serons obligés de modifier l'adapter, ce qui n'est pas idéal.

```php
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
```

Pour éviter cela nous allons utiliser le design pattern **Factory** : nous allons créer des classes chargées de l'instanciation de nos mailers.

```php
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
```

La classe ci-dessus se charge d'instancier PHPMailer. Nous pouvons ensuite créer une factory pour gérer l'instanciation des adapters :

```php
enum MailerType
{
    case PHPMailer;
    case SwiftMailer;
}

class MailerAdapterFactory
{
    public static function create(EmailConfig $config, MailerType $mailerType): MailerInterface
    {
        return match ($mailerType) {
            MailerType::PHPMailer => new PHPMailerAdapter(PHPMailerFactory::create($config)),
            MailerType::SwiftMailer => new SwiftMailerAdapter(SwiftMailerFactory::create($config)),
            default => throw new \InvalidArgumentException("Type de mailer inconnu : $mailerType->name")
        };
    }
}
```

Grâce à ces factory il nous est possible de récupérer facilement les adapter de la bibliothèque souhaitée :

```php
// On peut utiliser PHPMailer
$mailer = MailerAdapterFactory::create($config, MailerType::PHPMailer);

// Ou utiliser SwiftMailer
$mailer = MailerAdapterFactory::create($config, MailerType::SwiftMailer);

// Envoi de l'email grâce au service dans lequel on passe l'adapter en paramètre
$mailerService = new MailerService($mailer);
$mailerService->send($mailFrom, $mailTo, $mailContent);
```

**Conclusion** : Grâce au design pattern factory, nous avons une meilleure séparation des tâches dans chacune de nos classes. 