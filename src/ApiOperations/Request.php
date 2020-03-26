<?php

namespace R3m\Dpae\ApiOperations;

use R3m\Dpae\ApiRequestor;
use R3m\Dpae\ApiResponse;

/**
 * ! Ce trait doit uniquement être utilisé sur des classes dérivées de ApiResource.
 *
 * @package R3m\Dpae
 */
trait Request
{
    /**
     * @param string $method HTTP method ('get', 'post', etc.)
     * @param string $url URL de la requête
     * @param array|string|\JsonSerializable|null $data
     *
     * @return ApiResponse
     */
    protected function request(string $method, string $url, $data): ApiResponse
    {
        return static::staticRequest($method, $url, $data);
    }

    /**
     * @param string $method HTTP method ('get', 'post', etc.)
     * @param string $url URL de la requête
     * @param array|string|\JsonSerializable|null $data Données à envoyer avec la requête
     *
     * @return ApiResponse
     */
    protected static function staticRequest(string $method, string $url, $data = null): ApiResponse
    {
        return (new ApiRequestor())->request($method, $url, $data);
    }
}
