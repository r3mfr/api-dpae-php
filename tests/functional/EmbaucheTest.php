<?php

use donatj\MockWebServer\ResponseStack;
use R3m\Dpae;
use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;

class EmbaucheAppTest extends \Codeception\Test\Unit
{
    const JSON_PAYLOAD = <<<JSON
        {
          "id": "5ff7f313-3770-4ba0-a95b-60e3254f74ae",
          "dpae": {
            "id": "7af39bf6-e4ac-41cb-ba90-180321a76d9c",
            "statutTraitement": 0,
            "refDossier": "",
            "codeRetourAr": "",
            "dateEnregistrement": null,
            "statutTraitementDescription": "La DPAE est prête à être transmise à l'URSSAF.",
            "createdAt": "2019-08-30T21:57:30+00:00",
            "updatedAt": "2019-08-30T22:01:01+00:00"
          },
          "idExterne": "abcd",
          "raisonSociale": "R3M EVENEMENT",
          "siret": "67846960300074",
          "nomAbonneUrssaf": "DUPUIS",
          "prenomAbonneUrssaf": "MICHEL",
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

    const JSON_COLLECTION_PAYLOAD = '[' . self::JSON_PAYLOAD . ']';

    const JSON_COLLECTION_PAYLOAD_PAGE2 = '[
        {"id": "5ff7f313-3770-4ba0-a95b-60e3254f74af"}
    ]';
    const JSON_COLLECTION_PAYLOAD_EMPTY = '[]';

    /**
     * @var MockWebServer
     */
    protected static $__mock_webserver;
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testCreateEmbauche()
    {
        $responseMock = new Response(self::JSON_PAYLOAD, [], 201);
        self::$__mock_webserver->setResponseOfPath(
            Dpae\Embauche::classUrl(),
            $responseMock
        );

        $embauche = new Dpae\Embauche();
        $embauche
            ->setSalarieNom('Michel')
            ->setSalariePrenom('Dupont')
            // ...
            ->setIdExterne('abcd')
        ;
        $this->assertEquals('', $embauche->getId());
        $embauche->save();
        $this->assertEquals('5ff7f313-3770-4ba0-a95b-60e3254f74ae', $embauche->getId());
    }

    public function testRetreiveOneEmbaucheById()
    {
        $id = '5ff7f313-3770-4ba0-a95b-60e3254f74ae';

        $responseMock = new Response(self::JSON_PAYLOAD, [], 200);
        self::$__mock_webserver->setResponseOfPath(
            Dpae\Embauche::resourceUrl($id),
            $responseMock
        );

        $embauche = Dpae\Embauche::retrieve($id);

        $this->assertInstanceOf(Dpae\Embauche::class, $embauche);
        $this->assertEquals($id, $embauche->id);
    }

    public function testRetreiveAllEmbaucheByIdExterne()
    {
        $idExterne = 'abcd';

        $responseMockPage1 = new Response(self::JSON_COLLECTION_PAYLOAD, [], 200);
        $responseMockPage2 = new Response(self::JSON_COLLECTION_PAYLOAD_EMPTY, [], 200);
        self::$__mock_webserver->setResponseOfPath(
            Dpae\Embauche::classUrl(),
            new ResponseStack(
                $responseMockPage1,
                $responseMockPage2
            )
        );

        $embauches = Dpae\Embauche::all(['idExterne' => $idExterne]);

        $this->assertEquals($idExterne, self::$__mock_webserver->getLastRequest()->getGet()['idExterne']);
        $this->assertNotTrue(isset(self::$__mock_webserver->getLastRequest()->getGet()['page']));
        $this->assertCount(1, $embauches);

        $embauche = $embauches[0];
        $this->assertInstanceOf(Dpae\Embauche::class, $embauche);
        $this->assertEquals($idExterne, $embauche->idExterne);
    }

    public function testRetreiveOneEmbaucheByIdExterne()
    {

        $responseMockPage1 = new Response(self::JSON_COLLECTION_PAYLOAD, [], 200);
        $responseMockPage2 = new Response(self::JSON_COLLECTION_PAYLOAD_EMPTY, [], 200);
        self::$__mock_webserver->setResponseOfPath(
            Dpae\Embauche::classUrl(),
            new ResponseStack(
                $responseMockPage1,
                $responseMockPage2
            )
        );

        $idExterne = 'abcd';
        $embauche = Dpae\Embauche::one(['idExterne' => $idExterne]);
        $this->assertInstanceOf(Dpae\Embauche::class, $embauche);
        $this->assertEquals($idExterne, $embauche->idExterne);

        $idExterne = 'invalide';
        $embauche = Dpae\Embauche::one(['idExterne' => $idExterne]);
        $this->assertNull($embauche);
    }

    public function testRetreivePaginatedEmbauches()
    {
        $responseMockPage1 = new Response(self::JSON_COLLECTION_PAYLOAD, [], 200);
        $responseMockPage2 = new Response(self::JSON_COLLECTION_PAYLOAD_PAGE2, [], 200);
        $responseMockPage3 = new Response(self::JSON_COLLECTION_PAYLOAD_EMPTY, [], 200);

        self::$__mock_webserver->setResponseOfPath(
            Dpae\Embauche::classUrl(),
            new ResponseStack(
                $responseMockPage1,
                $responseMockPage2,
                $responseMockPage3
            )
        );

        $embauches = Dpae\Embauche::all();

        $expectedIds = [
            '5ff7f313-3770-4ba0-a95b-60e3254f74ae',
            '5ff7f313-3770-4ba0-a95b-60e3254f74af',
        ];
        $ids = [];
        foreach ($embauches->autoPagingIterator() as $embauche) {
            $ids[] = $embauche->id;
        }

        $this->assertEquals($expectedIds, $ids);
        $this->assertEquals([], $embauches->getLastResponse()->json);
        $this->assertEquals(3, self::$__mock_webserver->getLastRequest()->getGet()['page']);
    }

    protected function _before()
    {
        R3m\Dpae\ApiClient::setApiBase('http://localhost:8001');
        R3m\Dpae\ApiClient::setCredentials('foo', 'bar');

        $server = new MockWebServer(8001);
        $server->start();

        self::$__mock_webserver = $server;
    }

    protected function _after()
    {
        self::$__mock_webserver->stop();
    }

}