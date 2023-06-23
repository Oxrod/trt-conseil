<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/register', name: 'app_register')]
    public function index(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $newUser = new User();
        $registerForm = $this->createForm(RegisterFormType::class);
        $errorMessage = null;

        $registerForm->handleRequest($request);
        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $email = $registerForm->get('email')->getData();
            $role = $registerForm->get('roles')->getData();
            $password = $registerForm->get('password')->getData();

            $hashedPassword = $passwordHasher->hashPassword(
                $newUser,
                $password
            );

            $newUser
                ->setActive(false)
                ->setEmail($email)
                ->setRoles([...$newUser->getRoles(), $role])
                ->setPassword($hashedPassword);

            $check = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($check !== null) {
                $errorMessage = "Adresse mail déjà prise";
            } else {
                $entityManager->persist($newUser);
                $entityManager->flush();
            }

        }

        return $this->render('register/index.html.twig', [
            'form' => $registerForm,
            'errorMessage' => $errorMessage
        ]);
    }
}
