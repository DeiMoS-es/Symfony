<?php

namespace App\Controller;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }

    #[Route('/post/{id}', name: 'app_post')]
    public function index($id): Response{
        $post = $this->em->getRepository(Post::class)->find($id); // método mágico de symfony
        $custom_post = $this->em->getRepository(Post::class)->findPost($id);
        return $this->render('post/index.html.twig', [
            'post' => $custom_post
        ]);
    }

    #[Route('/insert/post', name: 'insert_post')]
    public function insert(){
        $post = new Post('Post insertado 2', 'opinion', 'Descripción del post insertado', 'file', 'miUrl', new \DateTime());
        $user = $this->em->getRepository(User::class)->find(1);
        $post->setUser($user);
        // Una vez creado el constructor para el post, no hace falta hacer set de cada propiedad, podemos pasarle los valores directamente al crear el post
        // $post->setTittle('Post insertado')->setDescription('Descripción del post insertado')->setCreationDate(new \DateTime())->setUrl('miUrl')->setFile('file')->setType('opinion')->setUser($user);
        $this->em->persist($post);
        $this->em->flush();// se encarga deescribir en la bbdd
        return new JsonResponse(['success' => true]);
    }

    #[Route('/update/post', name: 'insert_post')]
    public function update(){
        $post = $this->em->getRepository(Post::class)->find(4);
        $post->setTittle('Mi nuevo titulo');
        $this->em->flush();// se encarga deescribir en la bbdd
        return new JsonResponse(['success' => true]);
    }

    #[Route('/delete/post', name: 'insert_post')]
    public function delete(){
        $post = $this->em->getRepository(Post::class)->find(4);
        $this->em->remove($post);
        $this->em->flush();// se encarga deescribir en la bbdd
        return new JsonResponse(['success' => true]);
    }
}
