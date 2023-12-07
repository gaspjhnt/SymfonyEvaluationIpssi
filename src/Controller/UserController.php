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

// Is granted General pour sécurisé les profils et autre.
#[IsGranted('ROLE_USER')]
#[Route('/user')]
class UserController extends AbstractController
{

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        //Récupération et envoie de tous les utilisateurs pour les listes admin
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        //Verification de qui demande de voir le profil, s'il c'est l'utilisateur lui même ou un admin c'est bon
        if ($this->getUser()->getId() === $user->getId() || in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {

            $paniers = $user->getPaniers();

            $paniersDone = [];
            // Recherche des paniers finalisés
            for ($i = 0; $i < count($paniers); $i++) {
                if ($paniers[$i]->isEtat()) {
                    array_push($paniersDone, $paniers[$i]);
                }
            }
            //On envoie tous les paniers de l'utilisateur déjà payé pour les commandes déjà réalisées et ses données
            return $this->render('user/show.html.twig', [
                'commandes' => $paniersDone,
                'user' => $user,
            ]);
        }
        return $this->redirectToRoute('app_accueil');
    }


    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Même condition, sécurisation de l'edit.
        if ($this->getUser()->getId() === $user->getId() || in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {

            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            //Verification de la validité du form
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Profil édité avec succès');
                return $this->redirectToRoute('app_accueil', [], Response::HTTP_SEE_OTHER);
            }
            return $this->render('user/edit.html.twig', [
                'user' => $user,
                'form' => $form,
            ]);
        }
        // Otherwise, we redirect to the home page
        return $this->redirectToRoute('app_accueil');
    }

}
