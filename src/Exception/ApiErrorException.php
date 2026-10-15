<?php

namespace R3m\Dpae\Exception;

use Exception;

/**
 * Implémente les propriétés et les méthodes commnunes à toutes les exceptions
 *
 * @package R3m\Dpae
 */
abstract class ApiErrorException extends Exception implements ExceptionInterface
{
    protected ?string $httpBody = null;
    protected ?array $httpHeaders = null;
    protected ?int $httpStatus = null;
    protected ?array $jsonBody = null;
    protected ?array $violations = null;

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
        ?int $httpStatus = null,
        ?string $httpBody = null,
        ?array $jsonBody = null,
        ?array $httpHeaders = null,
    ): static {
        $instance = new static($message);
        $instance->setHttpStatus($httpStatus);
        $instance->setHttpBody($httpBody);
        $instance->setJsonBody($jsonBody);
        $instance->setHttpHeaders($httpHeaders);
        $instance->setViolations($instance->parseViolations());

        return $instance;
    }

    protected function parseViolations(): ?array
    {
        if ($this->jsonBody === null || !array_key_exists('violations', $this->jsonBody)) {
            return null;
        }

        return $this->jsonBody['violations'];
    }

    /**
     * Retourne le corps de la requête
     *
     * @return string|null
     */
    public function getHttpBody(): ?string
    {
        return $this->httpBody;
    }

    /**
     * @param string|null $httpBody
     */
    public function setHttpBody(?string $httpBody): void
    {
        $this->httpBody = $httpBody;
    }

    /**
     * Retourne les entêtes HTTP de la requête
     *
     * @return array|null
     */
    public function getHttpHeaders(): ?array
    {
        return $this->httpHeaders;
    }

    /**
     * @param array|null $httpHeaders
     */
    public function setHttpHeaders(?array $httpHeaders): void
    {
        $this->httpHeaders = $httpHeaders;
    }

    /**
     * Retourne le JSON décodé du corps de la requête
     *
     * @return array|null
     */
    public function getJsonBody(): ?array
    {
        return $this->jsonBody;
    }

    /**
     * @param array|null $jsonBody
     */
    public function setJsonBody(?array $jsonBody): void
    {
        $this->jsonBody = $jsonBody;
    }

    /**
     * @return array|null
     */
    public function getViolations(): ?array
    {
        return $this->violations;
    }

    /**
     * @param array|null $violations
     */
    public function setViolations(?array $violations): void
    {
        $this->violations = $violations;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $statusStr = ($this->getHttpStatus() === null) ? "" : "(Status {$this->getHttpStatus()}) ";

        return "{$statusStr}{$this->getMessage()}";
    }

    /**
     * Gets the HTTP status code.
     *
     * @return int|null
     */
    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }

    /**
     * @param int|null $httpStatus
     */
    public function setHttpStatus(?int $httpStatus): void
    {
        $this->httpStatus = $httpStatus;
    }
}
