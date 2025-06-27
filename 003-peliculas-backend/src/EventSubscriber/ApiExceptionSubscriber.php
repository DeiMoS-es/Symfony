<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;


#[AsEventListener(event: 'kernel.exception')]
class ApiExceptionSubscriber
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = 500;
        $message = 'Error interno del servidor';

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        }

        $request = $event->getRequest();
        if (str_starts_with($request->getPathInfo(), '/api/')) {
            $event->setResponse(new JsonResponse(['error' => $message], $statusCode));
        }
    }
}
