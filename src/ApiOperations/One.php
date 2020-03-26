<?php

namespace R3m\Dpae\ApiOperations;

use R3m\Dpae\ApiResource;

/**
 * @package R3m\Dpae
 */
trait One
{
    /**
     * @param array $filters
     *
     * @return ApiResource|null
     */
    public static function one(array $filters = []): ?ApiResource
    {
        $collection = static::all($filters);

        return $collection[0] ?? null;
    }
}