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
     * @var ResponseInterface
     */
    public $response;

    /**
     * @var array
     */
    public $json;

    /**
     * @param ResponseInterface $response
     * @param array $json
     */
    public function __construct(?\Psr\Http\Message\ResponseInterface $response, array $json)
    {
        $this->response = $response;
        $this->json = $json;
    }
}