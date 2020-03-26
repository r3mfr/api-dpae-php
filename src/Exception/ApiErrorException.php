<?php

namespace R3m\Dpae\Exception;

/**
 * Implémente les propriétés et les méthodes commnunes à toutes les exceptions
 *
 * @package R3m\Dpae
 */
abstract class ApiErrorException extends \Exception implements ExceptionInterface
{
    protected $httpBody;
    protected $httpHeaders;
    protected $httpStatus;
    protected $jsonBody;
    protected $violations;

    /**
     * Créé une nouvelle exception
     *
     * @param string $message Message de l'exception
     * @param int|null $httpStatus Status HTTP
     * @param string|null $httpBody Corps de la requête HTTP (string)
     * @param array|null $jsonBody JSON décodé
     * @param array|null $httpHeaders Headers HTTP (array)
     *
     * @return static
     */
    public static function factory(
        string $message,
        int $httpStatus = null,
        string $httpBody = null,
        array $jsonBody = null,
        array $httpHeaders = null
    ) {
        $instance = new static($message);
        $instance->setHttpStatus($httpStatus);
        $instance->setHttpBody($httpBody);
        $instance->setJsonBody($jsonBody);
        $instance->setHttpHeaders($httpHeaders);
        $instance->setViolations($instance->parseViolations());

        return $instance;
    }

    protected function parseViolations()
    {
        if (is_null($this->jsonBody) || !array_key_exists('violations', $this->jsonBody)) {
            return null;
        }

        return $this->jsonBody['violations'];
    }

    /**
     * Retourne le corps de la requête
     *
     * @return string|null
     */
    public function getHttpBody()
    {
        return $this->httpBody;
    }

    /**
     * @param mixed $httpBody
     */
    public function setHttpBody($httpBody): void
    {
        $this->httpBody = $httpBody;
    }

    /**
     * Retourne les entêtes HTTP de la requête
     *
     * @return array|null
     */
    public function getHttpHeaders()
    {
        return $this->httpHeaders;
    }

    /**
     * @param mixed $httpHeaders
     */
    public function setHttpHeaders($httpHeaders): void
    {
        $this->httpHeaders = $httpHeaders;
    }

    /**
     * Retourne le JSON décodé du corps de la requête
     *
     * @return array|null
     */
    public function getJsonBody()
    {
        return $this->jsonBody;
    }

    /**
     * @param mixed $jsonBody
     */
    public function setJsonBody($jsonBody): void
    {
        $this->jsonBody = $jsonBody;
    }

    /**
     * @return array|null
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @param mixed $violations
     */
    public function setViolations($violations): void
    {
        $this->violations = $violations;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $statusStr = ($this->getHttpStatus() == null) ? "" : "(Status {$this->getHttpStatus()}) ";
        return "{$statusStr}{$this->getMessage()}";
    }

    /**
     * Gets the HTTP status code.
     *
     * @return int|null
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /**
     * @param mixed $httpStatus
     */
    public function setHttpStatus($httpStatus): void
    {
        $this->httpStatus = $httpStatus;
    }
}
