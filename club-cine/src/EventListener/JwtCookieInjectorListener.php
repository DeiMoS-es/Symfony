<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class JwtCookieInjectorListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->headers->has('Authorization') && $request->cookies->has('ACCESS_TOKEN')) {
            $token = $request->cookies->get('ACCESS_TOKEN');
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }
    }
}
