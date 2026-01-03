<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * En Vercel, el sistema de archivos es de solo lectura.
     * Symfony necesita escribir caché, por lo que la redirigimos a /tmp.
     */
    public function getCacheDir(): string
    {
        if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
            return '/tmp/cache/' . $this->environment;
        }

        return parent::getCacheDir();
    }

    /**
     * Hacemos lo mismo con los logs para evitar errores de escritura.
     */
    public function getLogDir(): string
    {
        if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
            return '/tmp/log';
        }

        return parent::getLogDir();
    }
}