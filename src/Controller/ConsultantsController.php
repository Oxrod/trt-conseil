<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ConsultantsController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/admin/consultants', name: 'app_consultants')]
    public function index(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user->isActive()) {
            return $this->redirectToRoute('app_account_created');
        };

        $newConsultant = new User();
        $errorMessage = null;

        $createConsultantForm = $this->createFormBuilder()
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'Prénom'
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'label' => 'Nom de famille'
            ])
            ->add('email', EmailType::class, [
            'required' => true,
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe :'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer un consultant'
            ])
            ->getForm();

        $createConsultantForm->handleRequest($request);
        if ($createConsultantForm->isSubmitted() && $createConsultantForm->isValid()) {
            $firstName = $createConsultantForm['firstName']->getData();
            $lastName = $createConsultantForm['lastName']->getData();
            $email = $createConsultantForm->get('email')->getData();
            $password = $createConsultantForm->get('password')->getData();

            $hashedPassword = $passwordHasher->hashPassword(
                $newConsultant,
                $password
            );

            $newConsultant
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setActive(false)
                ->setEmail($email)
                ->setRoles([...$newConsultant->getRoles(), 'ROLE_CONSULTANT'])
                ->setPassword($hashedPassword);

            $check = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($check !== null) {
                $errorMessage = "Adresse mail déjà prise";
            } else {
                $entityManager->persist($newConsultant);
                $entityManager->flush();
            }

        }

        $consultants = $entityManager->getRepository(User::class)->findUsers('ROLE_CONSULTANT');

        return $this->render('consultants/index.html.twig', [
            'addConsultantForm' => $createConsultantForm,
            'errorMessage' => $errorMessage,
            'user' => $user,
            'consultants' => $consultants
        ]);
    }
}
