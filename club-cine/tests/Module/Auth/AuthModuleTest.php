<?php
namespace App\Tests\Module\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use App\Module\Auth\Repository\RefreshTokenRepository;

class AuthModuleTest extends WebTestCase
{
    private $client;
    private $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();

        $this->createSchema();
    }

    private function createSchema(): void
    {
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $schemaTool = new SchemaTool($this->em);
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }
    }

    private function postJson(string $url, array $data)
    {
        $this->client->request(
            'POST',
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            json_encode($data)
        );

        return $this->client->getResponse();
    }

    // --------------------------
    // Registro
    // --------------------------
    public function testRegisterUserSuccessfully(): void
    {
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

    public function testRegisterUserWithDuplicateEmail(): void
    {
        $payload = [
            'email' => 'usuario@prueba.com',
            'plainPassword' => '12345678',
            'name' => 'Deimos'
        ];

        $this->postJson('/auth/register', $payload);
        $response = $this->postJson('/auth/register', $payload);

        $this->assertResponseStatusCodeSame(400); // o 409 según tu implementación
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testRegisterUserWithInvalidPassword(): void
    {
        $payload = [
            'email' => 'usuario2@prueba.com',
            'plainPassword' => '123', // muy corta
            'name' => 'Ares'
        ];

        $response = $this->postJson('/auth/register', $payload);
        $this->assertResponseStatusCodeSame(400);
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);
    }

    // --------------------------
    // Login
    // --------------------------
    public function testLoginSuccessfully(): void
    {
        // Primero registramos
        $payload = [
            'email' => 'login@prueba.com',
            'plainPassword' => 'password123',
            'name' => 'Hermes'
        ];
        $this->postJson('/auth/register', $payload);

        $loginPayload = [
            'email' => $payload['email'],
            'password' => $payload['plainPassword']
        ];

        $response = $this->postJson('/auth/login', $loginPayload);
        $this->assertResponseStatusCodeSame(200);
        var_dump($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertArrayHasKey('refresh_token', $responseData);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $loginPayload = [
            'email' => 'noexiste@prueba.com',
            'password' => 'wrongpass'
        ];

        $response = $this->postJson('/auth/login', $loginPayload);
        $this->assertResponseStatusCodeSame(401);

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    // --------------------------
    // Refresh Token
    // --------------------------
    public function testRefreshTokenSuccessfully(): void
    {
        $payload = [
            'email' => 'refresh@prueba.com',
            'plainPassword' => 'password123',
            'name' => 'Poseidon'
        ];

        // Registro
        $this->postJson('/auth/register', $payload);

        // Login
        $loginPayload = [
            'email' => $payload['email'],
            'password' => $payload['plainPassword']
        ];
        $response = $this->postJson('/auth/login', $loginPayload);
        $responseData = json_decode($response->getContent(), true);

        $refreshToken = $responseData['refresh_token']['plain'];

        $refreshResponse = $this->postJson('/auth/refresh', ['refresh_token' => $refreshToken]);
        $this->assertResponseStatusCodeSame(200);

        $refreshData = json_decode($refreshResponse->getContent(), true);
        $this->assertArrayHasKey('token', $refreshData);
    }

    protected function tearDown(): void
    {
        if ($this->em) {
            $this->em->close();
            unset($this->em);
        }

        parent::tearDown();
    }
}
