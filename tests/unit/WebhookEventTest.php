<?php

use Nyholm\Psr7\ServerRequest;
use R3m\Dpae;

class WebhookEventTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testMissingHeadersInRequest()
    {
        $invalidRequest = new ServerRequest('POST', '/webhook-handler', [], json_encode([]));

        $this->expectException(Dpae\Exception\WebhookException::class);
        Dpae\WebhookEvent::receive('', $invalidRequest);
    }

    /**
     * @dataProvider requestSignatureDataProvider
     */
    public function testRequestSignatureAndSecretAreCheckedWhenAppropriate($secret, $headers, $expectException)
    {
        $requestWithInvalidSignature = new ServerRequest(
            'POST',
            '/webhook-handler',
            $headers,
            json_encode(['id' => '123456'])
        );
        if ($expectException) {
            $this->expectException($expectException);
        }
        Dpae\WebhookEvent::receive($secret, $requestWithInvalidSignature);
    }

    // tests

    public function requestSignatureDataProvider()
    {
        return [
            'null secret, no signature' => [
                null,
                [
                    'X-Dpae-Webhook-Id' => '123456',
                    'X-Dpae-Webhook-Action' => 'embauche.declaree',
                ],
                null
            ],
            'emtpy string secret, no signature' => [
                '',
                [
                    'X-Dpae-Webhook-Id' => '123456',
                    'X-Dpae-Webhook-Action' => 'embauche.declaree',
                ],
                null
            ],
            'no secret, signature' => [
                '',
                [
                    'X-Dpae-Webhook-Id' => '123456',
                    'X-Dpae-Webhook-Action' => 'embauche.declaree',
                    'X-Dpae-Signature' => 'abcd'
                ],
                Dpae\Exception\WebhookSignatureException::class
            ],
            'secret, invalid signature' => [
                'secret',
                [
                    'X-Dpae-Webhook-Id' => '123456',
                    'X-Dpae-Webhook-Action' => 'embauche.declaree',
                    'X-Dpae-Signature' => 'invalid'
                ],
                Dpae\Exception\WebhookSignatureException::class
            ],
            'secret, no signature' => [
                'secret',
                [
                    'X-Dpae-Webhook-Id' => '123456',
                    'X-Dpae-Webhook-Action' => 'embauche.declaree',
                ],
                Dpae\Exception\WebhookSignatureException::class
            ],
            'secret, valid signature' => [
                'secret',
                [
                    'X-Dpae-Webhook-Id' => '123456',
                    'X-Dpae-Webhook-Action' => 'embauche.declaree',
                    'X-Dpae-Signature' => 'sha1=' . sha1(json_encode(['id' => '123456']) . 'secret')
                ],
                null
            ],
        ];
    }

    public function testReceiveWebhookEvent()
    {
        $headers = [
            'X-Dpae-Webhook-Id' => '123456',
            'X-Dpae-Webhook-Action' => 'embauche.declaree',
        ];
        $embauche = [
            'id' => '123456',
            'salarieNom' => 'Dupont',
        ];
        $request = new ServerRequest('POST', '/webhook-handler', $headers, json_encode($embauche));

        $webhookEvent = Dpae\WebhookEvent::receive(null, $request);

        $this->assertInstanceOf(Dpae\Embauche::class, $webhookEvent->getEmbauche());
        $this->assertEquals('123456', $webhookEvent->getEmbauche()->id);
    }

    public function testInvalidJsonPayload()
    {
        $headers = [
            'X-Dpae-Webhook-Id' => '123456',
            'X-Dpae-Webhook-Action' => 'embauche.declaree',
        ];
        $request = new ServerRequest('POST', '/webhook-handler', $headers, 'invalid json');

        $this->expectException(Dpae\Exception\UnexpectedValueException::class);
        Dpae\WebhookEvent::receive(null, $request);
    }
}