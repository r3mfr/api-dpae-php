<?php

use R3m\Dpae;

class EmbaucheTest extends \Codeception\Test\Unit
{
    const JSON_PAYLOAD = <<<JSON
        {
          "id": "5ff7f313-3770-4ba0-a95b-60e3254f74ae",
          "dpae": {
            "id": "7af39bf6-e4ac-41cb-ba90-180321a76d9c",
            "statutTraitement": 0,
            "refDossier": "ABCDE",
            "codeRetourAr": "00",
            "dateEnregistrement": null,
            "statutTraitementDescription": "La DPAE est prête à être transmise à l'URSSAF.",
            "createdAt": "2019-08-30T21:57:30+00:00",
            "updatedAt": "2019-08-30T22:01:01+00:00"
          },
          "idExterne": "abcd",
          "raisonSociale": "R3M EVENEMENT",
          "siret": "67846960300074",
          "codeUrssaf": "460",
          "adresse1": "RUE DES ARTS",
          "adresse2": "BP 13011",
          "codePostal": "19000",
          "ville": "ARTVILLE",
          "telephone": "",
          "codeNaf": "9001Z",
          "codeCentreMedecineTravail": "MT348",
          "salarieNom": "DOE",
          "salarieNomEpoux": "",
          "salariePrenom": "JOHN",
          "salarieNumeroSecu": "1900318049025",
          "salarieSexe": "F",
          "salarieDateNaissance": "18031990",
          "salarieLieuNaissance": "BIENLOIN",
          "salarieDepartementNaissance": "98",
          "dateEmbauche": "16082018",
          "heureEmbauche": "1400",
          "typeContrat": "1",
          "dateFinCDD": "16082018",
          "dureePeriodeEssai": "",
          "createdAt": "2019-08-30T21:57:30+00:00",
          "updatedAt": "2019-08-30T22:01:01+00:00"
        }
JSON;

    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testIdIsProtectedButGettable()
    {
        $m = $this->construct(Dpae\Embauche::class, ['id' => '123456']);
        $this->assertEquals('123456', $m->id);
    }

    public function testIdIsNotEditableArrayAccess()
    {
        $m = $this->construct(Dpae\Embauche::class, ['id' => '123456']);
        $this->expectException(Dpae\Exception\BadMethodCallException::class);
        $m['id'] = 'boom';
    }

    // tests

    public function testConstructionOfApiResourceUrl()
    {
        $classUrl = '/embauches';
        $this->assertEquals($classUrl, Dpae\Embauche::classUrl());

        $id = '123456';
        $m = $this->construct(Dpae\Embauche::class, ['id' => $id]);
        $this->assertEquals("$classUrl/$id", Dpae\Embauche::resourceUrl($m->id));
        $this->assertEquals("$classUrl/$id", $m->instanceUrl());
    }

    public function testCreateEmbaucheFromJsonPayload()
    {
        $payload = json_decode(static::JSON_PAYLOAD, true);
        $this->assertIsArray($payload);

        $embauche = Dpae\Embauche::constructFrom($payload);
        $this->assertInstanceOf(Dpae\Embauche::class, $embauche);
        $this->assertEquals($embauche->id, $payload['id']);
        $this->assertIsInt($embauche->dpae->statutTraitement, $payload['dpae']['statutTraitement']);
        $this->assertInstanceOf(\DateTime::class, $embauche->createdAt);
        $this->assertInstanceOf(Dpae\ApiResource::class, $embauche->dpae);
        $this->assertEquals('2019-08-30T21:57:30+00:00', $embauche->dpae->createdAt->format(DATE_W3C));
        $this->assertInstanceOf(\DateTime::class, $embauche->updatedAt);
        $this->assertInstanceOf(\DateTime::class, $embauche->dpae->createdAt);
        $this->assertInstanceOf(\DateTime::class, $embauche->dpae->updatedAt);
    }

    public function testIsAccuseReceptionOKReturnsExpectedValue()
    {
        $payload = json_decode(static::JSON_PAYLOAD, true);
        $embauche = Dpae\Embauche::constructFrom($payload);

        $this->assertTrue($embauche->isDeclaree());
        $this->assertFalse($embauche->hasErreur());
    }

    public function testSavingAnEmbaucheIssuesARequestAndRefreshesTheObjectWithResponseData()
    {
        $lastResponse = new Dpae\ApiResponse(
            null, [
            'id' => '123456'
        ]
        );


        /** @var Dpae\Embauche $embauche */
        $embauche = $this->make(
            Dpae\Embauche::class,
            [
                'request' => \Codeception\Stub\Expected::once($lastResponse),
            ]
        );

        $embauche->save();

        $this->assertEquals('123456', $embauche->id);
        $this->assertEquals($lastResponse, $embauche->getLastResponse());
    }

    public function testSavingAnExistingEmbaucheInNotAllowed()
    {
        /** @var Dpae\Embauche $embauche */
        $embauche = $this->construct(
            Dpae\Embauche::class,
            ['id' => '123456'],
            [
                'request' => new Dpae\ApiResponse(null, [])
            ]
        );

        $this->expectException(Dpae\Exception\BadMethodCallException::class);

        $embauche->save();
    }

    public function testArrayAccessOnEmbaucheProperties()
    {
        $payload = json_decode(static::JSON_PAYLOAD, true);
        $embauche = Dpae\Embauche::constructFrom($payload);

        $this->assertEquals("5ff7f313-3770-4ba0-a95b-60e3254f74ae", $embauche['id']);
        $this->assertEquals("7af39bf6-e4ac-41cb-ba90-180321a76d9c", $embauche['dpae']['id']);
    }

    public function testSettingAnEmbauchePropertyIsNotAllowed()
    {
        $payload = json_decode(static::JSON_PAYLOAD, true);
        $embauche = Dpae\Embauche::constructFrom($payload);

        $this->expectException(Dpae\Exception\BadMethodCallException::class);
        $embauche->dureePeriodeEssai = 0;
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}