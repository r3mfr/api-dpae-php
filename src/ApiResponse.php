<?php


namespace R3m\Dpae;


use Psr\Http\Message\ResponseInterface;

/**
 * @package R3m\Dpae
 * @internal
 */
final class ApiResponse
{
    /**
     * @var ResponseInterface|null
     */
    public ?ResponseInterface $response = null;

    /**
     * @var array
     */
    public array $json = [];

    /**
     * @param ResponseInterface|null $response
     * @param array $json
     */
    public function __construct(?ResponseInterface $response, array $json)
    {
        $this->response = $response;
        $this->json = $json;
    }
}
