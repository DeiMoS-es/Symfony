<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    // 1. Configuración de Proxies (Dentro de la función)
    if (isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
        Request::setTrustedProxies(
            ['127.0.0.1', 'REMOTE_ADDR'],
            Request::HEADER_X_FORWARDED_FOR | 
            Request::HEADER_X_FORWARDED_HOST | 
            Request::HEADER_X_FORWARDED_PROTO | 
            Request::HEADER_X_FORWARDED_PORT
        );

        // 2. Configuración de sesión PHP para entornos sin disco
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        // Forzamos que no intente escribir en el disco por defecto de PHP
        ini_set('session.save_handler', 'user'); 
    }

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};