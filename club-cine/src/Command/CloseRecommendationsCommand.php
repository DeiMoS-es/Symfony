<?php

namespace App\Command;

use App\Module\Group\Services\RecommendationManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:close-recommendations',
    description: 'Busca y cierra las votaciones de películas que han superado su fecha límite.'
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
        $io->title('Procesando cierre de recomendaciones...');

        // Llamamos al Manager que acabas de crear
        $count = $this->recommendationManager->processExpiredRecommendations();

        if ($count > 0) {
            $io->success(sprintf('Se han cerrado %d recomendaciones correctamente.', $count));
        } else {
            $io->info('No hay recomendaciones pendientes de cierre en este momento.');
        }

        return Command::SUCCESS;
    }
}