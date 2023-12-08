<?php

namespace App\Controller;

use App\Entity\Produit;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\Translation\t;

#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        //Recupération de tous les produits en bdd et envoie à la vue
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        // Création du nv produit
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        // Verif du form de création
        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('Photo')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    //Renvoie d'erreur
                    $this->addFlash('danger', $translator->trans('produit.message.error.image'));
                }
                $produit->setPhoto($newFilename);
            }

            $entityManager->persist($produit);
            $entityManager->flush();

            //Renvoie de reussite
            $this->addFlash('success', $translator->trans('produit.message.success.product_add'));
            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        //Envoie d'un produit spécifique à la vue
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        //Prépa du formulaire
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        //Verif du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('Photo')->getData();

            //Gestion de l'image
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                    if ($produit->getPhoto() != null){
                        unlink($this->getParameter('upload_directory') . $produit->getPhoto());
                    }
                } catch (FileException $e) {
                    //Envoie d'erreur
                    $this->addFlash('danger', $translator->trans('produit.message.error.image_edit'));
                }
                $produit->setPhoto($newFilename);
            }
            $entityManager->persist($produit);
            $entityManager->flush();

            //Envoie de réussite
            $this->addFlash('success', $translator->trans('panier.message.success.panier_edit'));

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete/{id}', name: 'app_produit_delete')]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        //Supression du produit
        $entityManager->remove($produit);
        $entityManager->flush();

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
