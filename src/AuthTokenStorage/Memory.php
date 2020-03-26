<?php


namespace R3m\Dpae\AuthTokenStorage;

/**
 * Permet de stocker le token en mémoire.
 *
 * @package R3m\Dpae
 */
final class Memory implements AuthTokenStorageInterface
{
    private static $token = '';

    public function get(): string
    {
        return static::$token;
    }

    public function set(string $token): void
    {
        static::$token = $token;
    }
}