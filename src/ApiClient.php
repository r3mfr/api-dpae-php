<?php


namespace R3m\Dpae;


use Psr\Http\Client\ClientInterface;
use R3m\Dpae\AuthTokenStorage\AuthTokenStorageInterface;
use Symfony\Component\HttpClient\Psr18Client;

/**
 * @package R3m\Dpae
 */
class ApiClient
{
    const VERSION = '1.2.0';

    /**
     * @var string
     */
    protected static $apiBase = 'https://dpae.r3m.fr/api';

    /**
     * @var array
     */
    protected static $credentials = [];

    /**
     * @var AuthTokenStorageInterface
     */
    protected static $authTokenStorage;

    /**
     * @var string
     */
    protected static $token = '';

    /**
     * @var ClientInterface
     */
    protected static $httpClient;

    /**
     * @return array
     * @internal
     */
    public static function getCredentials(): array
    {
        return static::$credentials;
    }

    /**
     * @param string $username
     * @param string $password
     */
    public static function setCredentials(string $username, string $password): void
    {
        static::$credentials['username'] = $username;
        static::$credentials['password'] = $password;

        // expire token
        static::getAuthTokenStorage()->set('');
    }

    /**
     * @return AuthTokenStorageInterface
     */
    public static function getAuthTokenStorage(): AuthTokenStorageInterface
    {
        return static::$authTokenStorage ?? new AuthTokenStorage\Memory();
    }

    /**
     * @param AuthTokenStorageInterface $authTokenStorage
     */
    public static function setAuthTokenStorage(AuthTokenStorageInterface $authTokenStorage): void
    {
        static::$authTokenStorage = $authTokenStorage;
    }

    /**
     * @return string
     */
    public static function getApiBase(): string
    {
        return static::$apiBase;
    }

    /**
     * @param string $apiBase
     */
    public static function setApiBase(string $apiBase): void
    {
        static::$apiBase = $apiBase;
    }

    /**
     * @return ClientInterface
     */
    public static function getHttpClient(): ClientInterface
    {
        return static::$httpClient ?? new Psr18Client();
    }

    /**
     * @param ClientInterface $httpClient
     */
    public static function setHttpClient(ClientInterface $httpClient): void
    {
        static::$httpClient = $httpClient;
    }

}
