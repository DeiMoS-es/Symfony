<?php

namespace App\Movies\Command;

use App\Movies\Entity\Genre;
use App\Movies\Repository\GenreRepository;
use App\Movies\External\TmbdClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'genres:import')]
class ImportGenresCommand extends Command
{
    public function __construct(
        private TmbdClient $tmdbClient,
        private GenreRepository $genreRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = $this->tmdbClient->fetchGenres();
        $count = 0;

        foreach ($data as $item) {
            // Evitar duplicados por tmdbId
            $existing = $this->genreRepository->findOneBy(['tmdbId' => $item['id']]);
            if ($existing) {
                continue;
            }

            $genre = new Genre();
            $genre->setName($item['name']);
            $genre->setTmdbId($item['id']);

            $this->genreRepository->save($genre);
            $count++;
            $output->write('.');
        }

        $this->genreRepository->flush();
        $output->writeln("\n✅ Géneros importados: $count");

        return Command::SUCCESS;
    }
}
