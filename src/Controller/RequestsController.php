<?php

namespace App\Controller;

use App\Entity\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RequestsController extends AbstractController
{
    #[Route('/admin/requests', name: 'app_requests')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if(!$user->isActive()) {
            return $this->redirectToRoute('app_account_created');
        };

        $requests = $entityManager->getRepository(Request::class)->findAll();

        return $this->render('requests/index.html.twig', [
                'user' => $user,
                'requests' => $requests
        ]);
    }
}
