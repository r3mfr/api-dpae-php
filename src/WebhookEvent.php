<?php


namespace R3m\Dpae;


use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @package R3m\Dpae
 */
final class WebhookEvent
{
    const ACTION_EMBAUCHE_DECLAREE = 'embauche.declaree';

    /**
     * @var string
     */
    protected $action;

    /**
     * @var Embauche
     */
    protected $embauche;

    public function __construct(string $action, Embauche $embauche)
    {
        $this->action = $action;
        $this->embauche = $embauche;
    }

    /**
     * Reçoit un événement émis par un webhook, le valide éventuellement avec $secret,
     * et retourne une instance de WebhooEvent, représentant l'événement reçu.
     *
     * Cette méthode peut directement lire les données de la requête depuis les
     * variables globales et l'entrée php://input, ou alors vous pouvez lui passer
     * un objet ServerRequestInterface
     *
     * @param string|null $secret
     * @param ServerRequestInterface|null $request
     * @return WebhookEvent
     *
     * @throws Exception\WebhookException
     * @throws Exception\WebhookSignatureException
     */
    public static function receive(?string $secret, ServerRequestInterface $request = null): WebhookEvent
    {
        $request = $request ?? static::getServerRequest();

        if (!$request->hasHeader('X-Dpae-Webhook-Id') || !$request->hasHeader('X-Dpae-Webhook-Action')) {
            throw new Exception\WebhookException(
                "Requête invalide, header X-Dpae-Webhook-Id ou X-Dpae-Webhook-Action manquants."
            );
        }

        $payload = (string)$request->getBody();

        if ($secret !== null && $secret !== '') {
            if ($request->hasHeader('X-Dpae-Signature')) {
                $signature = $request->getHeaderLine('X-Dpae-Signature');
                if (!static::validateSignature($payload, $signature, $secret)) {
                    throw new Exception\WebhookSignatureException(
                        "La signature de la requête est invalide. La requête a pu être corrompue, ou la clé secrète est incohérente entre celle qui est fournie et celle qui est associée au webhook."
                    );
                }
            } else {
                throw new Exception\WebhookSignatureException(
                    "La signature de la requête doit être vérifiée avec la clé secrète, mais la requête n'a pas été signée. Vérifier dans votre guichet que la clé secrète est bien associée au webhook."
                );
            }
        } else {
            if ($request->hasHeader('X-Dpae-Signature')) {
                throw new Exception\WebhookSignatureException(
                    "Le webhook est configuré pour signer les requêtes avec une clé secrète, mais aucune clé secrète n'a été fournie. Veuillez fournir la clé secrète associée au webhook."
                );
            }
        }

        $action = $request->getHeaderLine('X-Dpae-Webhook-Action');

        $data = json_decode($payload, true);
        $jsonError = json_last_error();
        if ($data === null && $jsonError !== JSON_ERROR_NONE) {
            $msg = "Données de la requête invalides: $data (json_last_error(): $jsonError)";
            throw new Exception\UnexpectedValueException($msg);
        }

        switch ($action) {
            case static::ACTION_EMBAUCHE_DECLAREE:
                $embauche = Embauche::constructFrom($data);
                return new static($action, $embauche);
                break;
            default:
                throw new Exception\UnexpectedValueException("");
                break;
        }
    }

    private static function getServerRequest(): ServerRequestInterface
    {
        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        );

        return $creator->fromGlobals();
    }

    private static function validateSignature($payload, $signature, $secret)
    {
        $expectedSignature = 'sha1=' . sha1($payload . $secret);

        return $expectedSignature === $signature;
    }

    /**
     * Retourne l'embauche attachée au WebhooEvent
     * @return Embauche
     */
    public function getEmbauche(): Embauche
    {
        return $this->embauche;
    }
}