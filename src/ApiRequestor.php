<?php

namespace R3m\Dpae;

use JsonSerializable;
use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use R3m\Dpae\AuthTokenStorage\AuthTokenStorageInterface;

/**
 * @package R3m\Dpae
 * @internal
 */
class ApiRequestor
{
    /**
     * @var AuthTokenStorageInterface
     */
    protected AuthTokenStorageInterface $authTokenStorage;

    /**
     * @var string
     */
    protected string $apiBase = '';

    /**
     * @var ClientInterface
     */
    protected ClientInterface $httpClient;

    /**
     * @param AuthTokenStorageInterface|null $authTokenStorage
     * @param ClientInterface|null $httpClient
     * @param string $apiBase
     */
    public function __construct(
        ?AuthTokenStorageInterface $authTokenStorage = null,
        ?ClientInterface $httpClient = null,
        string $apiBase = '',
    ) {
        $this->authTokenStorage = $authTokenStorage ?? ApiClient::getAuthTokenStorage();
        $this->httpClient = $httpClient ?? ApiClient::getHttpClient();
        $this->apiBase = $apiBase ?: ApiClient::getApiBase();
    }

    /**
     * @param string $method
     * @param string $url
     * @param mixed $data
     *
     * @return ApiResponse
     *
     * @throws Exception\ApiErrorException
     * @throws Exception\AuthenticationException
     */
    public function request(string $method, string $url, mixed $data): ApiResponse
    {
        $authToken = $this->authTokenStorage->get();

        try {
            $response = $this->requestRaw($method, $url, $data, $authToken);
        } catch (Exception\AuthenticationException) {
            $authToken = $this->getAuthToken();
            $this->authTokenStorage->set($authToken);
            // retente avec un token
            $response = $this->requestRaw($method, $url, $data, $authToken);
        }

        return $response;
    }

    /**
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @param string $authToken
     *
     * @return ApiResponse
     * @throws Exception\ApiConnectionException
     * @throws Exception\ApiErrorException
     */
    protected function requestRaw(string $method, string $url, mixed $data, string $authToken): ApiResponse
    {
        $absUrl = $this->apiBase . $url;

        $body = null;
        if ($data !== null) {
            if (is_string($data)) {
                $body = $data;
            } elseif (is_array($data) || $data instanceof JsonSerializable) {
                $body = json_encode($data);
                $jsonError = json_last_error();
                if ($body === false || ($body === null && $jsonError !== JSON_ERROR_NONE)) {
                    $msg = "Données de la requête invalides: $data (json_last_error(): $jsonError)";
                    throw new Exception\UnexpectedValueException($msg);
                }
            } else {
                throw new Exception\InvalidArgumentException(
                    'Le corps de la requête doit être parmi: null, string, array, JsonSerializable. "'
                    . get_debug_type($data) . '" passé.',
                );
            }
        }

        $headers = $this->defaultHeaders($authToken);
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';

        $request = new Request(strtoupper($method), $absUrl, $headers, $body);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new Exception\ApiConnectionException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->parseResponse($response);
    }

    /**
     * @param string $authToken
     *
     * @return array
     */
    protected function defaultHeaders(string $authToken = ''): array
    {
        $uaString = 'r3mfr/api-dpae-php/' . ApiClient::VERSION;

        $langVersion = phpversion();
        $uname_disabled = in_array('php_uname', explode(',', (string) ini_get('disable_functions')));
        $uname = $uname_disabled ? '(disabled)' : php_uname();

        $ua = [
            'bindings_version' => ApiClient::VERSION,
            'lang' => 'php',
            'lang_version' => $langVersion,
            'publisher' => 'r3m',
            'uname' => $uname,
        ];

        $headers = [
            'X-R3m-ApiDpae-User-Agent' => json_encode($ua),
            'User-Agent' => $uaString,
        ];

        if ($authToken !== '') {
            $headers['Authorization'] = 'Bearer ' . $authToken;
        }

        return $headers;
    }

    /**
     *
     * @param ResponseInterface $response
     *
     * @return ApiResponse
     *
     * @throws Exception\ApiErrorException
     */
    protected function parseResponse(ResponseInterface $response): ApiResponse
    {
        $payload = (string) $response->getBody();
        $data = json_decode($payload, true);
        $jsonError = json_last_error();
        if ($data === null && $jsonError !== JSON_ERROR_NONE) {
            $msg = "Impossible de décoder la réponse de l'API: $payload "
                . "(Status HTTP: {$response->getStatusCode()}, json_last_error(): $jsonError)";
            throw new Exception\UnexpectedValueException($msg);
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->handleResponseError($response, $data);
        }

        return new ApiResponse($response, $data);
    }

    /**
     * @param ResponseInterface $response
     * @param array $data
     *
     * @return never
     * @throws Exception\ApiErrorException
     */
    protected function handleResponseError(ResponseInterface $response, array $data): never
    {
        $code = (int) ($data['code'] ?? $response->getStatusCode());
        $message = $data['message'] ?? $data['title'] ?? $response->getReasonPhrase();
        $body = (string) $response->getBody();
        $headers = $response->getHeaders();

        throw match ($code) {
            400, 422 => Exception\BadRequestException::factory($message, $code, $body, $data, $headers),
            401 => Exception\AuthenticationException::factory($message, $code, $body, $data, $headers),
            404 => Exception\InvalidRequestException::factory($message, $code, $body, $data, $headers),
            default => Exception\UnknownApiErrorException::factory($message, $code, $body, $data, $headers),
        };
    }

    protected function getAuthToken(): string
    {
        $credentials = ApiClient::getCredentials();
        if (empty($credentials['username']) || empty($credentials['password'])) {
            throw new Exception\AuthenticationException(
                'Veuillez configurer des identifiants valides avec ApiClient::setCredentials() avant toute requête.',
            );
        }

        $body = [
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ];
        $response = $this->requestRaw('POST', '/login_check', $body, '');

        return $response->json['token'];
    }
}
