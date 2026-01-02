<?php

namespace App\Command;

use App\Module\Group\Services\RecommendationManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:group:close-expired',
    description: 'Cierra las recomendaciones de películas cuya fecha límite de votación ha pasado.'
)]
class CloseRecommendationsCommand extends Command
{
    public function __construct(
        private readonly RecommendationManager $recommendationManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Procesando recomendaciones caducadas');

        try {
            $count = $this->recommendationManager->processExpiredRecommendations();

            if ($count > 0) {
                $io->success(sprintf('Se han cerrado correctamente %d recomendaciones.', $count));
            } else {
                $io->info('No se han encontrado recomendaciones caducadas para cerrar.');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Ocurrió un error al procesar las recomendaciones: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
