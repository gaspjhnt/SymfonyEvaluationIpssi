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

#[IsGranted('ROLE_USER')]
#[Route('/user')]
class UserController extends AbstractController
{

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        if ($this->getUser()->getId() === $user->getId() || in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {

            return $this->render('user/show.html.twig', [
                'user' => $user,
            ]);
        }
        return $this->redirectToRoute('app_accueil');
    }


    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->getId() === $user->getId() || in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {

            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

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
        return $this->redirectToRoute('app_accueil');
    }

}
