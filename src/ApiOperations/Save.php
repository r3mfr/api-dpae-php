<?php

namespace R3m\Dpae\ApiOperations;

/**
 * ! Ce trait doit uniquement être utilisé sur des classes dérivées de ApiResource.
 *
 * @package R3m\Dpae
 */
trait Save
{
    public function save()
    {
        $url = static::classUrl();
        $response = $this->request('POST', $url, $this);
        $this->setLastResponse($response);
        $this->refreshFrom($response->json);
    }
}