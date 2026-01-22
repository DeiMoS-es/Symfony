<?php

namespace App\Module\Notification\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    public function __construct(
        private readonly MailerInterface $mailer
    ) {}

    public function sendNotification(string $to, string $subject, string $content): void
    {
        $email = (new Email())
            ->from('no-reply@clubcine.com')
            ->to($to)
            ->subject($subject)
            ->text($content)
            ->html("<p>{$content}</p>");

        $this->mailer->send($email);
    }
}