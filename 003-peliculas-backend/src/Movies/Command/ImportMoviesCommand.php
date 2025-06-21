<?php
// src/Movies/Command/ImportMoviesCommand.php
namespace App\Movies\Command;

use App\Movies\Service\MovieService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'movies:import')]
class ImportMoviesCommand extends Command
{
    public function __construct(private MovieService $movieService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $this->movieService->importMovies();
        $output->writeln("✅ Películas importadas: $count");
        $movies = $this->movieService->importMovies();

        return Command::SUCCESS;
    }
}
