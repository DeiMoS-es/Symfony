<?php

namespace App\Module\Notification\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function sendGroupInvitation(string $recipientEmail, string $groupName, string $invitationToken): void
    {
        $email = (new TemplatedEmail())
            ->from('no-reply@clubdecine.com')
            ->to($recipientEmail)
            ->subject("¡Te han invitado al grupo: $groupName!")
            ->htmlTemplate('emails/group_invitation.html.twig')
            ->context([
                'groupName' => $groupName,
                'token' => $invitationToken,
                // En local:
                'url' => 'http://localhost:8000/join/group/' . $invitationToken
                // En producción:
                // 'url' => 'https://symfony-6a8yfzw9m-deimoss-projects.vercel.app/join/group/' . $invitationToken 
            ]);

        $this->mailer->send($email);
    }
}

?>