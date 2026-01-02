<?php

namespace App\Tests\Module\Auth\Service;

use App\Module\Auth\DTO\RegistrationRequest;
use App\Module\Auth\Entity\User;
use App\Module\Auth\Service\RegistrationService;
use App\Module\Group\Entity\Group;
use App\Module\Group\Entity\GroupInvitation;
use App\Module\Group\Entity\GroupMember;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class RegistrationServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private RegistrationService $registrationService;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->registrationService = $container->get(RegistrationService::class);

        // Crear tablas en la base de datos de memoria
        $schemaTool = new SchemaTool($this->entityManager);
        $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->updateSchema($metadatas);
    }

    public function testRegisterWithInvitationSuccessfully(): void
    {
        // --- 1. PREPARACIÓN ---
        $owner = new User("owner@test.com", "pass", ["ROLE_USER"]);
        $this->entityManager->persist($owner);
        $group = new Group("Grupo Gastronómico", $owner);
        $this->entityManager->persist($group);

        $invitation = new GroupInvitation("juan@test.com", $group, new \DateTimeImmutable('+1 day'));
        $this->entityManager->persist($invitation);
        $this->entityManager->flush();

        $tokenGenerado = $invitation->getToken();

        $request = new RegistrationRequest();
        $request->email = "juan@test.com";
        $request->name = "Juan Test";
        $request->plainPassword = "password123";

        // --- 2. ACCIÓN ---
        $this->registrationService->register($request, $tokenGenerado);

        // --- 3. VERIFICACIÓN ---
        $this->entityManager->clear();

        // Verificación 1: ¿Existe Juan?
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'juan@test.com']);
        $this->assertNotNull($user, "Juan debería existir en la DB.");

        // Verificación 2: ¿Hay 2 miembros en total?
        $memberships = $this->entityManager->getRepository(GroupMember::class)->findAll();
        $this->assertCount(2, $memberships, "Debería haber exactamente 2 miembros.");

        // Verificación 3: ¿Se borró la invitación?
        $invitationInDb = $this->entityManager->getRepository(GroupInvitation::class)->findOneBy(['token' => $tokenGenerado]);
        $this->assertNull($invitationInDb, "La invitación debería haber sido eliminada.");

        // Verificación 4: ¿El grupo del usuario es el correcto?
        $this->assertEquals("Grupo Gastronómico", $user->getGroups()[0]->getName());
    }
}