<?php
namespace App\EventListener;

use App\Module\Movie\Exception\TmdbUnauthorizedException;
use App\Module\Movie\Exception\TmdbNotFoundException;
use App\Module\Movie\Exception\TmdbUnavailableException;
use App\Module\Movie\Exception\TmdbException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class TmdbExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof TmdbUnauthorizedException) {
            $response = new JsonResponse(['error' => $exception->getMessage()], 401);
        } elseif ($exception instanceof TmdbNotFoundException) {
            $response = new JsonResponse(['error' => $exception->getMessage()], 404);
        } elseif ($exception instanceof TmdbUnavailableException) {
            $response = new JsonResponse(['error' => $exception->getMessage()], 503);
        } elseif ($exception instanceof TmdbException) {
            $response = new JsonResponse(['error' => $exception->getMessage()], 500);
        } else {
            return; // dejamos que Symfony maneje otras excepciones
        }

        $event->setResponse($response);
    }
}
