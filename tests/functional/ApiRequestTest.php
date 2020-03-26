<?php

use R3m\Dpae;
use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;

class ApiRequestTest extends \Codeception\Test\Unit
{
    /**
     * @var MockWebServer
     */
    protected static $__mock_webserver;
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testViolationsRaiseAnException()
    {
        $responseMock = new Response(
            '{
          "type": "https://tools.ietf.org/html/rfc2616#section-10",
          "title": "An error occurred",
          "detail": "salarieNom: This value should not be blank.",
          "violations": [
            {
              "propertyPath": "salarieNom",
              "message": "This value should not be blank."
            }
          ]
        }', [], 400
        );

        self::$__mock_webserver->setResponseOfPath(
            '/some/route',
            $responseMock
        );
        $apiRequestor = new Dpae\ApiRequestor(null, null, 'http://localhost:8001');
        try {
            $apiRequestor->request('GET', '/some/route', '{"salarieNom": ""}');
        } catch (Dpae\Exception\BadRequestException $e) {
            $this->assertEquals($e->getMessage(), 'An error occurred');
            $this->assertIsArray($e->getViolations());
            $this->assertEquals('salarieNom', $e->getViolations()[0]['propertyPath']);
            return;
        }

        $this->assertTrue(false, 'Did not catch expected exception');
    }

    public function testReturnsApiResponse()
    {
        $responseMock = new Response('{"foo": "bar"}', [], 200);
        self::$__mock_webserver->setResponseOfPath(
            '/some/route',
            $responseMock
        );
        $apiRequestor = new Dpae\ApiRequestor(null, null, 'http://localhost:8001');
        $response = $apiRequestor->request('GET', '/some/route', '');

        $this->assertInstanceOf(Dpae\ApiResponse::class, $response);
        $this->assertEquals((string)$response->response->getBody(), '{"foo": "bar"}');
        $this->assertEquals($response->response->getStatusCode(), 200);
        $this->assertIsArray($response->json);
        $this->assertEquals('bar', $response->json['foo']);
    }

    // tests

    // REQUEST/RESPONSE

    public function testRaiseUnexpectedValueExceptionWithInvalidJson()
    {
        self::$__mock_webserver->setResponseOfPath(
            '/invalid/json',
            new Response('{invalid json}', [], 200)
        );
        $apiRequestor = new Dpae\ApiRequestor(null, null, 'http://localhost:8001');
        $this->expectException(Dpae\Exception\UnexpectedValueException::class);
        $apiRequestor->request('GET', '/invalid/json', '');
    }

    public function testRaiseExceptionWithNonJsonSerializablePayload()
    {
        $apiRequestor = new Dpae\ApiRequestor(null, null, 'http://localhost:8001');
        $this->expectException(Dpae\Exception\InvalidArgumentException::class);
        $apiRequestor->request('GET', '/status/404', new \StdClass());
    }

    public function testRaiseExceptionWithInvalidEndpoint()
    {
        self::$__mock_webserver->setResponseOfPath(
            '/status/404',
            new Response('{"code": 404, "message": "Resource not found"}', [], 404)
        );
        $apiRequestor = new Dpae\ApiRequestor(null, null, 'http://localhost:8001');
        $this->expectException(Dpae\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage('Resource not found');
        $apiRequestor->request('GET', '/status/404', '');
    }

    public function testRaiseExceptionWithUnaccessibleApi()
    {
        $apiRequestor = new Dpae\ApiRequestor(null, null, 'http://localhost:9999');
        $this->expectException(Dpae\Exception\ApiConnectionException::class);
        $apiRequestor->request('GET', '/something', '');
    }

    // CONNEXION

    public function testRaiseAuthenticationExceptionIfNoCredentials()
    {
        self::$__mock_webserver->setResponseOfPath(
            '/status/401',
            new Response(
                '{
              "code": 401,
              "message": "JWT Token not found"
            }', [], 401
            )
        );
        $apiRequestor = new Dpae\ApiRequestor(null, null, 'http://localhost:8001');
        $this->expectException(Dpae\Exception\AuthenticationException::class);
        $this->expectExceptionMessageRegExp('/^Veuillez configurer des identifiants valides/');
        $apiRequestor->request('GET', '/status/401', '');
    }

    public function testRaiseAuthenticationExceptionWithInvalidCredentials()
    {
        self::$__mock_webserver->setResponseOfPath(
            '/status/401',
            new Response(
                '{
              "code": 401,
              "message": "JWT Token not found"
            }', [], 401
            )
        );
        self::$__mock_webserver->setResponseOfPath(
            '/login_check',
            new Response(
                '{
              "token": 401,
              "message": "Bad credentials"
            }', [], 401
            )
        );
        Dpae\ApiClient::setCredentials('invalid', 'credentials');
        $apiRequestor = new Dpae\ApiRequestor(null, null, 'http://localhost:8001');
        $this->expectException(Dpae\Exception\AuthenticationException::class);
        $this->expectExceptionMessage('Bad credentials');
        $apiRequestor->request('POST', '/status/401', '');
    }

    // AUTH

    public function testTokenIsRefreshedAndStored()
    {
        $tokenStorage = new Dpae\AuthTokenStorage\Memory();
        $this->assertEquals('', $tokenStorage->get());

        self::$__mock_webserver->setResponseOfPath(
            '/status/401',
            new Response(
                '{
              "code": 401,
              "message": "JWT Token not found"
            }', [], 401
            )
        );
        self::$__mock_webserver->setResponseOfPath(
            '/login_check',
            new Response(
                '{
              "token": "refreshed token"
            }', [], 201
            )
        );
        Dpae\ApiClient::setCredentials('valid', 'credentials');

        $apiRequestor = new Dpae\ApiRequestor($tokenStorage, null, 'http://localhost:8001');
        try {
            $apiRequestor->request('POST', '/status/401', '');
        } catch (Dpae\Exception\AuthenticationException $e) {
        }

        $this->assertEquals('refreshed token', $tokenStorage->get());
    }

    protected function _before()
    {
        $server = new MockWebServer(8001);
        $server->start();

        self::$__mock_webserver = $server;
    }

    protected function _after()
    {
        self::$__mock_webserver->stop();
    }

}