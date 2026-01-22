<?php

namespace App\Tests\Module\Notification\Service;

use App\Module\Notification\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailServiceTest extends KernelTestCase
{
    public function testMailerIsConfiguredCorrectly(): void
    {
        self::bootKernel();
        
        // Verificamos que el servicio Mailer existe en el contenedor
        $container = static::getContainer();
        $mailer = $container->get(MailerInterface::class);
        
        $this->assertInstanceOf(MailerInterface::class, $mailer);
    }

    public function testEmailCanBeComposed(): void
    {
        $email = (new Email())
            ->from('hello@clubcine.com')
            ->to('test@example.com')
            ->subject('Test Subject')
            ->text('Test Content');

        $this->assertEquals('Test Subject', $email->getSubject());
        $this->assertEquals('hello@clubcine.com', $email->getFrom()[0]->getAddress());
        $this->assertEquals('test@example.com', $email->getTo()[0]->getAddress());
    }

    public function testEmailServiceSendsEmailSuccessfully(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        // USA EL NOMBRE DE LA CLASE DIRECTAMENTE
        $emailService = $container->get(\App\Module\Notification\Service\EmailService::class);
        
        $emailService->sendNotification(
            'user@example.com',
            '¡Ya tenemos película!',
            'La ganadora de esta semana es Pulp Fiction.'
        );

        $this->assertEmailCount(1);
    }
}