<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
use App\Form\ProduitType;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Doctrine\DBAL\Types\DateType;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Contracts\Translation\TranslatorInterface;


#[Route('/panier')]
class PanierController extends AbstractController
{
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/', name: 'app_panier_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository): Response
    {
        // Envoie de tous les paniers pour les listés (tri fait dans le twig)
        return $this->render('panier/index.html.twig', [
            'paniers' => $panierRepository->findAll(),
        ]);
    }

    #[Route('/show', name: 'app_panier_show', methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager): Response
    {
        // Récupération des paniers de l'utilisateur courant
        $paniers = $this->getUser()->getPaniers();
        $paniersToSend = null;

        for ($i = 0; $i < count($paniers); $i++) {

            // Vérifiez si le panier est vide après la suppression
            if ($paniers[$i]->getContenuPaniers()->isEmpty()) {
                $entityManager->remove($paniers[$i]);
                $entityManager->flush();
            } else {
                if (!$paniers[$i]->isEtat()) {
                    $paniersToSend = $paniers[$i];
                }
            }

        }

        // Envoie du panier
        return $this->render('panier/show.html.twig', [
            'panier' => $paniersToSend,
        ]);
    }

    /*
    *  Ajout d'une ligne / produit au panier
    */
    #[Route('/{id}', name: 'app_panier_add')]
    public function addProduct(string $id, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $produit = $entityManager->getRepository(Produit::class)->findOneBy(['id' => $id]);

        $paniers = $this->getUser()->getPaniers();
        $panier = null;

        // Verif si il y a déjà un panier non acheté
        for ($i = 0; $i < count($paniers); $i++) {
            if (!$paniers[$i]->isEtat()) {
                $panier = $paniers[$i];
            }
        }

        // Si non on en créer un
        if ($panier == null) {
            $panier = new Panier();
            $panier->setUser($this->getUser());
            $panier->setEtat(false);
            $entityManager->persist($panier);
            $entityManager->flush();
        }

        // Récupération des contenuPanier en fonction du panier et du produit
        $contenuPanier = $entityManager->getRepository(ContenuPanier::class)->findOneBy([
            'panier' => $panier,
            'produit' => $produit,
        ]);

        if ($contenuPanier) {
            // Si le produit est déjà dans le panier on ajoute 1 à la quantité
            $contenuPanier->setQuantite($contenuPanier->getQuantite() + 1);
            $contenuPanier->setDate(new \DateTime());
        } else {
            // Si non on créer un contenu et on ajoute le produit
            $contenuPanier = new ContenuPanier();
            $contenuPanier->setPanier($panier);
            $contenuPanier->setDate(new \DateTime());
            $contenuPanier->setProduit($produit);
            $contenuPanier->setQuantite(1);
            $entityManager->persist($contenuPanier);
        }

        $this->addFlash('success', $translator->trans('panier.success.add_article'));
        $entityManager->flush();

        return $this->redirectToRoute('app_panier_show', [], Response::HTTP_SEE_OTHER);
    }


    /*
     * Retire un element du panier (ex -1 sur quantité ou suppression du produit si quantité = 1)
     */
    #[Route('/remove/{id}', name: 'app_panier_remove')]
    public function remove(string $id, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $produit = $entityManager->getRepository(Produit::class)->findOneBy(['id' => $id]);

        $paniers = $this->getUser()->getPaniers();
        $panier = null;

        // On verif si il y a bien un panier non vendu
        for ($i = 0; $i < count($paniers); $i++) {
            if (!$paniers[$i]->isEtat()) {
                $panier = $paniers[$i];
            }
        }

        if ($panier !== null) {
            $contenuPaniers = $panier->getContenuPaniers();

            foreach ($contenuPaniers as $contenuPanier) {
                if ($contenuPanier->getProduit() === $produit) {
                    // Si le produit est bien celui attendu et que sa quantité est supérieur a 1 on retire 1
                    if ($contenuPanier->getQuantite() > 1) {
                        $contenuPanier->setQuantite($contenuPanier->getQuantite() - 1);
                    } else {
                        // Sinon on supprime le produit
                        $entityManager->remove($contenuPanier);
                    }
                    $entityManager->flush();
                    $this->addFlash('success', $translator->trans('panier.success.remove_one'));
                    break;
                }
            }
        }
        return $this->redirectToRoute('app_panier_show', [], Response::HTTP_SEE_OTHER);
    }

    /*
     * Supression d'une ligne du panier peu importe la quantité
     */
    #[Route('/removeLine/{id}', name: 'app_panier_removeLine')]
    public function removeLine(string $id, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $produit = $entityManager->getRepository(Produit::class)->findOneBy(['id' => $id]);

        $paniers = $this->getUser()->getPaniers();
        $panier = null;

        // Recherche d'un panier non finalisé
        for ($i = 0; $i < count($paniers); $i++) {
            if (!$paniers[$i]->isEtat()) {
                $panier = $paniers[$i];
            }
        }

        if ($panier !== null) {
            $contenuPaniers = $panier->getContenuPaniers();

            foreach ($contenuPaniers as $contenuPanier) {
                if ($contenuPanier->getProduit() === $produit) {
                    // On supprime le produit du contenu du panier (même si plusieurs fois)
                    $entityManager->remove($contenuPanier);
                }
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('panier.success.remove_line'));

                break;
            }
        }
        return $this->redirectToRoute('app_panier_show', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/order/{id}', name: 'app_panier_order')]
    public function order(Panier $panier, EntityManagerInterface $entityManager): Response
    {
        //Passage de panier a commande
        $panier->setEtat(true);
        $panier->setDateAchat(new \DateTime());
        $entityManager->persist($panier);
        $entityManager->flush();

        return $this->render('panier/order.html.twig', [
            'panier' => $panier,
        ]);
    }

}
