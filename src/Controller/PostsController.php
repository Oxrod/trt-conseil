<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Request as RequestType;
use App\Entity\User;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostsController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->isActive()) {
            return $this->redirectToRoute('app_account_created');
        };

        $newPost = new Post();
        $addPostForm = $this->createForm(PostType::class);
        $addPostForm->handleRequest($request);
        if ($addPostForm->isSubmitted() && $addPostForm->isValid()) {
            $title = $addPostForm['title']->getData();
            $location = $addPostForm['location']->getData();
            $description = $addPostForm['description']->getData();

            $newPost
                ->setRecruiter($user)
                ->setActive(false)
                ->setTitle($title)
                ->setDescription($description)
                ->setLocation($location);

            $entityManager->persist($newPost);

            // Generate new Request
            $newRequest = new RequestType();
            $newRequest
                ->setType('post-creation')
                ->setIssuer($user)
                ->setTarget($newPost)
                ->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($newRequest);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        $posts = $entityManager->getRepository(Post::class)->findBy(['active' => true]);

        return $this->render('posts/index.html.twig', [
            'addPostForm' => $addPostForm,
            'user' => $user,
            'posts' => $posts
        ]);
    }
}
