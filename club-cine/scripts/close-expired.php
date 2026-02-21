<?php

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    return new class($context['APP_ENV'] ?? 'dev') {
        private $kernel;

        public function __construct(string $env)
        {
            $appPath = dirname(__DIR__);
            require_once $appPath . '/src/Kernel.php';

            $this->kernel = new \App\Kernel($env, $env === 'dev');
            $this->kernel->boot();
        }

        public function __invoke()
        {
            $container = $this->kernel->getContainer();
            $recommendationManager = $container->get('App\Module\Group\Services\RecommendationManager');

            try {
                $count = $recommendationManager->processExpiredRecommendations();

                if ($count > 0) {
                    echo "[OK] Se cerraron $count recomendaciones expiradas y se calcularon las estadísticas.\n";
                } else {
                    echo "[INFO] No hay recomendaciones expiradas para procesar.\n";
                }
            } catch (\Exception $e) {
                echo "[ERROR] " . $e->getMessage() . "\n";
                exit(1);
            }
        }
    };
};
