<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Request;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AcceptRequestController extends AbstractController
{
    #[Route('admin/requests/accept/{slug}', name: 'app_accept_request', methods: 'POST')]
    public function index(EntityManagerInterface $entityManager, string $slug): Response
    {
        $requestId = intval($slug);
        $request = $entityManager->getRepository(Request::class)->findOneBy(['id' => $requestId]);

        switch ($request->getType()) {
            case 'account-creation':
                $targetUser = $entityManager->getRepository(User::class)->findOneBy(['id' => $request->getIssuer()->getId()]);
                $targetUser->setActive(true);
                $entityManager->persist($targetUser);
                break;
            case 'post-creation':
                $targetPost = $entityManager->getRepository(Post::class)->findOneBy(['id' => $request->getTarget()->getId()]);
                $targetPost->setActive(true);
                $request->getIssuer()->addPost($targetPost);
                $entityManager->persist($targetPost);
                break;
            case 'post-appliance':
                $targetPost = $entityManager->getRepository(Post::class)->findOneBy(['id' => $request->getTarget()->getId()]);
                $targetPost->addCandidate($request->getIssuer());
                $userToUpdate = $request->getIssuer();
                $entityManager->persist($userToUpdate);
                $entityManager->persist($targetPost);
                break;
        }

        $entityManager->remove($request);
        $entityManager->flush();

        return $this->redirectToRoute('app_requests');
    }
}
