<?php

namespace R3m\Dpae\ApiOperations;

/**
 * ! Ce trait doit uniquement être utilisé sur des classes dérivées de ApiResource.
 *
 * @package R3m\Dpae
 */
trait Retrieve
{
    /**
     * @param array|string $id ID de l'ApiResource à charger
     *
     * @return static
     */
    public static function retrieve($id)
    {
        $url = static::resourceUrl($id);
        $response = static::staticRequest('GET', $url);

        $instance = new static($id);
        $instance->setLastResponse($response);
        $instance->refreshFrom($response->json);

        return $instance;
    }
}
