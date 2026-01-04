<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// Configuración para que Symfony confíe en el proxy de Vercel
if (isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
    Request::setTrustedProxies(
        ['127.0.0.1', 'REMOTE_ADDR'],
        Request::HEADER_X_FORWARDED_FOR | 
        Request::HEADER_X_FORWARDED_HOST | 
        Request::HEADER_X_FORWARDED_PROTO | 
        Request::HEADER_X_FORWARDED_PORT
    );
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};