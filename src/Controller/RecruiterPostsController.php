<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecruiterPostsController extends AbstractController
{
    #[Route('/admin/my-posts', name: 'app_recruiter_posts')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->isActive()) {
            return $this->redirectToRoute('app_account_created');
        };

        $userPosts = $entityManager->getRepository(Post::class)->findBy(['recruiter' => $user]);

        return $this->render('recruiter_posts/index.html.twig', [
            'userPosts' => $userPosts,
            'user' => $user
        ]);
    }
}
