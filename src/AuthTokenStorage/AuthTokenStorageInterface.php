<?php


namespace R3m\Dpae\AuthTokenStorage;

/**
 * @package R3m\Dpae
 */
interface AuthTokenStorageInterface
{
    /**
     * Charge le token depuis le stockage
     *
     * @return string
     */
    public function get(): string;

    /**
     * Enregistre le token dans le stockage
     *
     * @param string $token
     */
    public function set(string $token): void;
}