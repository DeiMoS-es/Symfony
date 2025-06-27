<?php
namespace App\Users\Service;

use App\Movies\Repository\MovieRepository;
use App\Users\Entity\UserMovie;
use App\Users\Repository\UserMovieRepository;
use App\Users\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserMovieService{

    private UserRepository $userRepository;
    private UserMovieRepository $userMovieRepository;
    private MovieRepository $movieRepository;
    private EntityManagerInterface $em;

    public function __construct(UserRepository $userRepository, UserMovieRepository $userMovieRepository, MovieRepository $movieRepository, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->userMovieRepository = $userMovieRepository;
        $this->movieRepository = $movieRepository;
        $this->em = $em;
    }


    public function rateMovieByUser(int $userId, int $movieId, int $rating): void { 
        $user = $this->userRepository->find($userId);

        $movie = $this->movieRepository->find($movieId);
        if (!$user || !$movie) {
            throw new NotFoundHttpException('Usuario o película no encontrados.');
        }

        $userMovie = $this->userMovieRepository->findOneBy([
            'user' => $user,
            'movie' => $movie,
        ]);
        
        if(!$userMovie){
            $userMovie = new UserMovie($user, $movie);
            $this->em->persist($userMovie);
        }

        $userMovie->setRating($rating);
        $this->em->flush();
    }

   // public function getRatedMoviesByUser(int $userId): array { ... }

    //public function markAsFavorite(...) { ... }
}


?>