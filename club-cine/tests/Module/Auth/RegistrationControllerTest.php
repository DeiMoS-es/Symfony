<?php
namespace App\Tests\Module\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

class RegistrationControllerTest extends WebTestCase
{
    private $client;
    private $em;

    protected function setUp(): void{
        parent::setUp();

        $this->client = static::createClient();

        // Obtenemos el EntityManager del contenedor
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();

        // Creamos el esquema en memoria
        $this->createSchema();
    }

    private function createSchema(): void{
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $schemaTool = new SchemaTool($this->em);
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }
    }

    private function postJson(string $url, array $data){
        $this->client->request(
            'POST',
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        return $this->client->getResponse();
    }

    public function testRegisterUserSuccessfully(): void{
        $payload = [
            'email' => 'usuario@prueba.com',
            'plainPassword' => '12345678',
            'name' => 'Deimos'
        ];

        $response = $this->postJson('/auth/register', $payload);

        $this->assertResponseStatusCodeSame(201);

        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals($payload['email'], $responseData['email']);
        $this->assertEquals($payload['name'], $responseData['name']);
    }

    public function testRegisterUserWithDuplicateEmail(): void{
        $payload = [
            'email' => 'usuario@prueba.com',
            'plainPassword' => '12345678',
            'name' => 'Deimos'
        ];

        // Primer registro
        $this->postJson('/auth/register', $payload);

        // Segundo registro con mismo email
        $response = $this->postJson('/auth/register', $payload);

        $this->assertResponseStatusCodeSame(400); // O 409 según tu implementación
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    protected function tearDown(): void{
        if ($this->em) {
            $this->em->close();
            unset($this->em);
        }

        parent::tearDown();
    }
}
