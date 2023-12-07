<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// This controller differentiates actions according to user roles. Users with the 'ROLE_ADMIN' 
// role have access to more functions, such as viewing the complete list of users and modifying other users' profiles. 
// Actions are restricted for normal users, who can only modify their own profile.

#[IsGranted('ROLE_USER')]
#[Route('/user')]
class UserController extends AbstractController
{

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // Get all users
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        // We retrieve the user's profile
        if ($this->getUser()->getId() === $user->getId() || in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {

            $paniers = $user->getPaniers();

            $paniersDone = [];
            // Recherche des paniers finalisés
            for ($i = 0; $i < count($paniers); $i++) {
                if ($paniers[$i]->isEtat()) {
                    array_push($paniersDone, $paniers[$i]);
                }
            }


            // We display the user's profile
            return $this->render('user/show.html.twig', [
                'commandes' => $paniersDone,
                'user' => $user,
            ]);
        }
        // Otherwise, we redirect to the home page
        return $this->redirectToRoute('app_accueil');
    }


    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // We retrieve the user's profile
        if ($this->getUser()->getId() === $user->getId() || in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {

            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            // We check if the form has been submitted and if it is valid.
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Profil édité avec succès');
                return $this->redirectToRoute('app_accueil', [], Response::HTTP_SEE_OTHER);
            }

            // If the form has not been submitted, we display the form.
            return $this->render('user/edit.html.twig', [
                'user' => $user,
                'form' => $form,
            ]);
        }
        // Otherwise, we redirect to the home page
        return $this->redirectToRoute('app_accueil');
    }

}
