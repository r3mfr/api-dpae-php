<?php

require '../vendor/autoload.php';

use R3m\Dpae;

//
// Paramétrage des identifiants de connexion
//

$username = getenv('API_DPAE_USERNAME') ?: 'username';
$password = getenv('API_DPAE_PASSWORD') ?: 'password';
Dpae\ApiClient::setCredentials($username, $password);

//
// Modification de l'URL de l'API DPAE
//

Dpae\ApiClient::setApiBase(getenv('API_DPAE_BASE') ?: 'https://dpae.r3m.fr/api');

//
// Utiliser un autre client HTTP
//

$curl = new \Symfony\Component\HttpClient\CurlHttpClient(['verify_peer' => false]);
$client = new \Symfony\Component\HttpClient\Psr18Client($curl);
Dpae\ApiClient::setHttpClient($client);

//
// Stocker le token JWT ailleurs qu'en mémoire
//

$tokenStorage = new class implements Dpae\AuthTokenStorage\AuthTokenStorageInterface {
    public function get(): string
    {
        // Stocker le token en base de données ou dans un fichier.
        return @file_get_contents('/tmp/api_dpae_token') ?: '';
    }

    public function set(string $token): void
    {
        // Lire le token depuis la base de données ou depuis un fichier
        file_put_contents('/tmp/api_dpae_token', $token);
    }
};
Dpae\ApiClient::setAuthTokenStorage($tokenStorage);

//
// Création d'une Embauche
//

$idExterne = '123456'; // ID de l'embauche dans l'application cliente
//$idExterne = '{"id": "123456"}'; // Possibilité de mettre du JSON

$embauche = new Dpae\Embauche();
$embauche
    ->setRaisonSociale('R3M EVENEMENT')
    ->setSiret('67846960300074')
    ->setCodeUrssaf('460')
    ->setNomAbonneUrssaf('DUPUIS')
    ->setPrenomAbonneUrssaf('MICHEL')
    ->setAdresse1('RUE DES ARTS')
    ->setAdresse2('BP 13011')
    ->setCodePostal('19000')
    ->setVille('ARTVILLE')
    ->setTelephone('')
    ->setCodeNaf('9001Z')
    ->setCodeCentreMedecineTravail('MT348')
    ->setSalarieNom('DOE')
    ->setSalarieNomEpoux('')
    ->setSalariePrenom('JOHN')
    ->setSalarieNumeroSecu('1900318049025')
    ->setSalarieSexe('F')
    ->setSalarieDateNaissance('18031990')
    ->setSalarieLieuNaissance('BIENLOIN')
    ->setSalarieDepartementNaissance('98')
    ->setDateEmbauche('16082018')
    ->setHeureEmbauche('1400')
    ->setTypeContrat('1')
    ->setDateFinCDD('16082018')
    ->setDureePeriodeEssai('')
    ->setIdExterne($idExterne)
;

try {
    $embauche->save();
} catch (Dpae\Exception\BadRequestException $e) {
    foreach ($e->getViolations() as $violation) {
        echo("Erreur sur la propriété {$violation['propertyPath']}: {$violation['message']}");
    }
    exit(1);
} catch (Dpae\Exception\ExceptionInterface $e) {
    echo("Erreur lors de la création de l'embauche : {$e->getCode()} {$e->getMessage()}");
    exit(1);
}

echo("Embauche {$embauche->id} créée. Statut de la DPAE: {$embauche->getDpae()->statutTraitementDescription}.");

//
// Vérifier manuellement le statut de la DPAE d'une embauche
//

$idExterne = '123456'; // ID de l'embauche dans l'application cliente

try {
    /** @var Dpae\Embauche $embauche */
    $embauche = Dpae\Embauche::one(['idExterne' => $idExterne]);
} catch (Dpae\Exception\ExceptionInterface $e) {
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
