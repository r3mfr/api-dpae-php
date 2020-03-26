<?php

use R3m\Dpae;

class ApiClientTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testChangingCredentialsResetsTheToken()
    {
        $storage = new R3m\Dpae\AuthTokenStorage\Memory();
        $storage->set('abcd');
        Dpae\ApiClient::setAuthTokenStorage($storage);
        $this->assertEquals('abcd', $storage->get());
        Dpae\ApiClient::setCredentials('foo', 'bar');
        $this->assertEquals('', $storage->get());
    }

    public function testGetHttpClientReturnsADefaultOne()
    {
        $this->assertInstanceOf(\Psr\Http\Client\ClientInterface::class, Dpae\ApiClient::getHttpClient());
    }

    // tests

    public function testACustomHttpClientCanBeSetAndGettable()
    {
        $httpClient = new \Symfony\Component\HttpClient\Psr18Client();
        $this->assertNotSame($httpClient, Dpae\ApiClient::getHttpClient());

        Dpae\ApiClient::setHttpClient($httpClient);
        $this->assertSame($httpClient, Dpae\ApiClient::getHttpClient());
    }

    public function testGetAuthTokenStorageReturnsADefaultOne()
    {
        $this->assertInstanceOf(
            Dpae\AuthTokenStorage\AuthTokenStorageInterface::class,
            Dpae\ApiClient::getAuthTokenStorage()
        );
    }

    public function testACustomAuthTokenStorageCanBeSetAndGettable()
    {
        $storage = new Dpae\AuthTokenStorage\Memory();
        $this->assertNotSame($storage, Dpae\ApiClient::getAuthTokenStorage());

        Dpae\ApiClient::setAuthTokenStorage($storage);
        $this->assertSame($storage, Dpae\ApiClient::getAuthTokenStorage());
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}