<?php

namespace R3m\Dpae\ApiOperations;

use R3m\Dpae\ApiCollection;
use R3m\Dpae\ApiResource;

/**
 * @package R3m\Dpae
 */
trait All
{
    /**
     * @param array $filters
     *
     * @return ApiCollection Collection d'ApiResources
     */
    public static function all(array $filters = []): ApiCollection
    {
        $url = static::classUrl();
        $query = $filters ? http_build_query($filters) : '';

        $response = static::staticRequest('GET', "$url?$query");

        $collection = new ApiCollection(static::class, $filters, 1);
        $collection->refreshFrom($response->json);
        $collection->setLastResponse($response);

        return $collection;
    }
}