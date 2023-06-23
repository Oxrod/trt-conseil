<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Request;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApplyController extends AbstractController
{
    #[Route('/apply/{post}', name: 'app_apply')]
    public function index(EntityManagerInterface $entityManager, Post $post): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->isActive()) {
            return $this->redirectToRoute('app_account_created');
        };

        $checkRequest = $entityManager->getRepository(Request::class)->findOneBy(['issuer' => $user, 'target' => $post]);

        if($post->getCandidates()->contains($user) || $checkRequest != null) {
            return $this->redirectToRoute('/home');
        };

        $request = new Request();
        $request
            ->setType('post-appliance')
            ->setIssuer($user)
            ->setTarget($post)
            ->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($request);
        $entityManager->flush();


        return $this->render('apply/index.html.twig', [
            'user' => $user,
            'post' => $post
        ]);
    }
}
