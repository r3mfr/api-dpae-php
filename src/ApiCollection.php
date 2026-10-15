<?php

namespace R3m\Dpae;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;

/**
 * @package R3m\Dpae
 * @internal
 */
class ApiCollection implements IteratorAggregate, ArrayAccess
{
    use ApiOperations\Request {
        request as traitRequest;
    }

    /**
     * @var array
     */
    protected array $filters = [];

    /**
     * @var int
     */
    protected int $currentPage = 1;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var ApiResponse|null
     */
    protected ?ApiResponse $lastResponse = null;

    /**
     * @var string
     */
    private string $apiResourceClass;

    public function __construct(string $apiResourceClass, array $filters = [], int $page = 1)
    {
        if (!class_exists($apiResourceClass) || !is_a($apiResourceClass, ApiResource::class, true)) {
            throw new InvalidArgumentException("$apiResourceClass doit étendre " . ApiResource::class);
        }
        $this->apiResourceClass = $apiResourceClass;
        $this->currentPage = $page;
        $this->filters = $filters;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException("Cette collection est en lecture seule.");
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException("Cette collection est en lecture seule.");
    }

    /**
     * Retourne un itérateur sur les données de la page courante
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Générateur permettant de boucler sur toutes les ApiResource, page par page.
     * A chaque fin de page, la page suivante est automatiquement chargée pour continuer l'itération.
     *
     * @return Generator<ApiResource>
     */
    public function autoPagingIterator(): Generator
    {
        while (true) {
            foreach ($this->data as $item) {
                yield $item;
            }
            $this->nextPage();

            if ($this->isEmpty()) {
                break;
            }
        }
    }

    protected function nextPage(): void
    {
        if ($this->isEmpty()) {
            return;
        }

        $this->currentPage++;
        $this->request();
    }

    protected function isEmpty(): bool
    {
        return empty($this->data);
    }

    protected function request(): void
    {
        $url = $this->apiResourceClass::classUrl();
        $query = http_build_query(array_merge($this->filters, ['page' => $this->currentPage]));

        $response = static::staticRequest('GET', "$url?$query");

        $this->refreshFrom($response->json);
        $this->setLastResponse($response);
    }

    /**
     * @param array $values
     *
     * @internal
     */
    public function refreshFrom(array $values): void
    {
        $this->data = [];
        foreach ($values as $v) {
            $this->data[] = $this->apiResourceClass::constructFrom($v);
        }
    }

    /**
     * Retourne la dernière réponse reçue de l'API
     *
     * @return ApiResponse|null
     */
    public function getLastResponse(): ?ApiResponse
    {
        return $this->lastResponse;
    }

    /**
     * @param ApiResponse $response
     *
     * @internal
     */
    public function setLastResponse(ApiResponse $response): void
    {
        $this->lastResponse = $response;
    }
}
