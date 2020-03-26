<?php

require '../vendor/autoload.php';

use R3m\Dpae;

//
// Recevoir la notification par webhook
//

$secret = 'abcd';

try {
    $webhookEvent = Dpae\WebhookEvent::receive($secret);
} catch (Dpae\Exception\ExceptionInterface $e) {
    echo("Erreur lors de la réception du webhook : {$e->getMessage()}");

    // Retourner un code >= 400 informera l'API DPAE de l'erreur
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

// Retourner une 503 permet à l'API de retenter la notification par webhook un peu plus tard
//header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable', true, 503);
//header('Retry-after: 3600');
