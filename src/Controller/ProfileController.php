<?php

namespace App\Controller;

use App\Form\CandidateFormType;
use App\Form\RecruiterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/profile', name: 'app_profile')]
    public function index(EntityManagerInterface $entityManager, SluggerInterface $slugger, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user->isActive()) {
            return $this->redirectToRoute('app_account_created');
        };


        // CANDIDATE SECTION
        $candidateForm = $this->createForm(CandidateFormType::class);

        $candidateForm->handleRequest($request);
        if ($candidateForm->isSubmitted() && $candidateForm->isValid()) {
            $firstName = $candidateForm['firstName']->getData();
            $lastName = $candidateForm['lastName']->getData();

            if ($firstName != '' && $firstName != null) {
                $user->setFirstName($firstName);
            }
            if ($lastName != '' && $lastName != null) {
                $user->setLastName($lastName);
            }

            $curriculumFile = $candidateForm->get('cv')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($curriculumFile) {
                $originalFilename = pathinfo($curriculumFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $curriculumFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $curriculumFile->move(
                        $this->getParameter('cv_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $user->setCv($newFilename);

            }

            $entityManager->persist($user);
            $entityManager->flush();
        }
        // END OF CANDIDATE SECTION

        // RECRUITER SECTION
        $recruiterForm = $this->createForm(RecruiterFormType::class);
        $recruiterForm->handleRequest($request);
        if ($recruiterForm->isSubmitted() && $recruiterForm->isValid()) {
            $companyName = $recruiterForm['companyName']->getData();
            $companyAdress = $recruiterForm['companyAdress']->getData();

            if ($companyName != '' && $companyName != null) {
                $user->setCompanyName($companyName);
            }
            if ($companyAdress != '' && $companyAdress != null) {
                $user->setCompanyAdress($companyAdress);
            }

            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'candidateForm' => $candidateForm->createView(),
            'recruiterForm' => $recruiterForm->createView()
        ]);
    }
}
