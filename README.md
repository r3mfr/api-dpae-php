# API DPAE - PHP

Cette librairie fournit un moyen simple pour utiliser l'API DPAE [dpae.r3m.fr](https://dpae.r3m.fr) pour toute application écrite dans le language PHP.

## Pré-requis

PHP 7.1.0 et plus récent.

## Installation

La méthode d'installation recommandée est avec [Composer](http://getcomposer.org/). Exécutez la commande suivante :

```bash
composer require r3mfr/api-dpae-php
```

Assurez-vous que le fichier d'[autoload de Composer](https://getcomposer.org/doc/01-basic-usage.md#autoloading)  soit bien inclus dans votre application :

```php
require_once('vendor/autoload.php');
```

## Documentation

Se référer à la [documentation de l'API DPAE](https://dpae.r3m.fr/docs/) pour avoir un aperçu complet du fonctionnement de l'API, et de son intégration dans un logiciel.

### Authentification

Toute requête doit être accompagnée par un jeton JWT.

Ce jeton est créé automatiquement pour vous par la librairie PHP, en utilisant vos identifiants.

_Pour obtenir vos identifiants, vous devez vous créer un compte sur [API DPAE](https://dpae.r3m.fr/)._

```php
$username = 'foo';
$password = 'bar';
\R3m\Dpae\ApiClient::setCredentials($username, $password);
```

### Stockage du jeton d'authentification

Le jeton JWT a une durée de vie limitée. Par défaut il est stocké en mémoire, et est donc redemandé à chaque requête. Cela n'est pas optimal et peut poser des problèmes de quota d'authentification.

Il est fortement conseillé de permettre le stockage du jeton JWT ailleurs qu'en mémoire, en définissant un `TokenStorage`. Exemple :

```php
$myTokenStorage = new class implements \R3m\Dpae\AuthTokenStorage\AuthTokenStorageInterface {
    public function get(): string
    {
        // Lire le token depuis la base de données ou depuis un fichier
    }

    public function set(string $token): void
    {
        // Stocker le token en base de données ou dans un fichier
    }
};
\R3m\Dpae\ApiClient::setAuthTokenStorage($myTokenStorage);
```

### Soumettre une embauche

Pour effectuer une DPAE, vous devez soumettre une Embauche à l'API DPAE.
Référez-vous à la [documentation](https://dpae.r3m.fr/docs/#soumettre-une-embauche) pour avoir le détail et le format des propriétés d'une Embauche.

```php
$embauche = new \R3m\Dpae\Embauche();
$embauche
    ->setRaisonSociale('R3M EVENEMENT')
    ->setSiret('67846960300074')
    // ... définir les autres valeurs de l'embauche ...
    ->setIdExterne('123456');

try {
    $embauche->save();
} catch (\R3m\Dpae\Exception\BadRequestException $e) {
    foreach ($e->getViolations() as $violation) {
        echo("Erreur sur la propriété {$violation['propertyPath']}: {$violation['message']}");
    }
    exit(1);
} catch (\R3m\Dpae\Exception\ExceptionInterface $e) {
    echo("Erreur lors de la création de l'embauche : {$e->getCode()} {$e->getMessage()}");
    exit(1);
}

echo("Embauche {$embauche->id} créée. Statut de la DPAE: {$embauche->getDpae()->statutTraitementDescription}.");
```

### Vérifier le statut d'une Embauche

Bien qu'il soit conseillé d'utiliser un [Webhook](https://dpae.r3m.fr/docs/#webhooks) pour être notifié du retour de la DPAE, il est possible d'interroger l'API DPAE manuellement.

```php
$idExterne = '123456'; // ID de l'embauche dans l'application cliente

try {
    /** @var \R3m\Dpae\Embauche $embauche */
    $embauche = \R3m\Dpae\Embauche::one(['idExterne' => $idExterne]);
} catch (\R3m\Dpae\Exception\ExceptionInterface $e) {
    echo("Erreur {$e->getCode()} lors de la recherche de l'embauche : {$e->getMessage()}");
    exit(1);
}

if ($embauche) {
    if ($embauche->isDeclaree()) {
        echo("DPAE validée par l'URSSAF. Numéro de dossier : {$embauche->getDpae()->refDossier}");
    } else {
        if ($embauche->hasErreur()) {
            echo("Erreur avec la DPAE de l'embauche {$embauche->getId()} : {$embauche->getDpae()->codeRetourAr}-{$embauche->getDpae()->codeRetourArLibelle}");
        } else {
            echo("Statut de la DPAE pour l'embauche {$embauche->getId()} : {$embauche->getDpae()->statutTraitementDescription}");
        }
    }
} else {
    echo("Aucune embauche trouvée pour idExterne=$idExterne");
}
```

## Recevoir la notification Webhook

Il est possible d'être notifié par l'API dès qu'un accusé de réception est disponible. Il suffit de déclarer une URL de votre application en tant que webhook. Ainsi, il ne sera pas nécessaire d'interroger régulièrement l'API pour connaître le statut de vos déclarations d'embauche : c'est l'API qui notifiera votre application, en lui passant toutes les informations nécessaires au traitement de l'accusé de réception.

Vous devez donc exposer une URL de votre application. Lors de l'appel de cette URL, exécutez le code suivant :

```php

// Il est conseillé de configurer une clé secrête pour valider la requête Webhook 
$secret = 'phrase_secrete';

try {
    $webhookEvent = \R3m\Dpae\WebhookEvent::receive($secret);
} catch (\R3m\Dpae\Exception\ExceptionInterface $e) {
    echo sprintf("Erreur lors de la réception du webhook : {$e->getMessage()}");

    // Retourner un code >= 400 informera l'API DPAE de l'erreur de traitement
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Dequest', true, 400);
    exit(1);
}

$embauche = $webhookEvent->getEmbauche();

// Vous pouvez maintenant traitez le statut de la DPAE dans votre application :
// - mettre à jour le statut de la DPAE dans votre base de données
// - notifiez le déclarant si une erreur doit être corrigée
// - etc.

if ($embauche->isDeclaree()) {
    echo sprintf("DPAE validée par l'URSSAF. Numéro de dossier : {$embauche->getDpae()->refDossier}");
} else {
    // l'embauche n'est pas déclarée, la DPAE est en erreur
    echo sprintf("Erreur avec la DPAE de l'embauche {$embauche->getId()} : {$embauche->getDpae()->codeRetourAr}-{$embauche->getDpae()->codeRetourArLibelle}");
}

// Informer l'API DPAE du succès du traitement du webhook
header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK', true, 200);
```

### En cas de maintenance

En cas de maintenance de votre application, vous pouvez retourner une 503.
Cela permettra à l'API DPAE de retenter la notification par webhook un peu plus tard.

```php
header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable', true, 503);
header('Retry-after: 3600');
```

## Utiliser un autre client HTTP 

Par défaut, `symfony/http-client` est utilisé pour dialoguer avec API DPAE.
Si vous préférez, vous pouvez utiliser un autre client HTTP implémentant `\Psr\Http\Client\ClientInterface`.

```
$myhttpClient = new MyHttpClient(['option1' => true]);
Dpae\ApiClient::setHttpClient($myhttpClient);
```

## Contributions

Les pull request sont les bienvenues.

### Tests

Codeception est utilisé pour les tests.

```
$ vendor/bin/codecept run
```
