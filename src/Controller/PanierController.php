<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
use App\Form\PanierType;
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
use Symfony\Component\Validator\Constraints\Date;


// This controller manages basket-related operations in the Symfony application, such as displaying all baskets, 
// displaying a specific basket and deleting a basket after confirmation via a CSRF token.

#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('/', name: 'app_panier_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository): Response
    {
        // Create a new basket
        return $this->render('panier/index.html.twig', [
            'paniers' => $panierRepository->findAll(),
        ]);
    }

    #[Route('/show', name: 'app_panier_show', methods: ['GET'])]
    public function show(): Response
    {
        // We retrieve the basket of the connected user
        $panier = $this->getUser()->getPanier();
        $paniers = $this->getUser()->getPaniers();
        $paniersToSend = null;

        for($i = 0; $i < count($paniers); $i++){
            if (!$paniers[$i]->isEtat()){
                $paniersToSend = $paniers[$i];
            }
        }

        return $this->render('panier/show.html.twig', [
            'panier' => $paniersToSend,
        ]);
    }


    #[Route('/{id}', name: 'app_panier_add')]
    public function addProduct(String $id, EntityManagerInterface $entityManager): Response
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

        // Si aucun panier non finalisé n'est trouvé, créez-en un
        if ($panier == null) {
            $panier = new Panier();
            $panier->setUser($this->getUser());
            $panier->setEtat(false);
            $entityManager->persist($panier);
            $entityManager->flush();
        }

        // Vérifier si le produit est déjà dans le panier
        $contenuPanier = $entityManager->getRepository(ContenuPanier::class)->findOneBy([
            'panier' => $panier,
            'produit' => $produit,
        ]);

        if ($contenuPanier) {
            // Si le produit est déjà dans le panier, incrémente la quantité et met à jour la date
            $contenuPanier->setQuantite($contenuPanier->getQuantite() + 1);
            $contenuPanier->setDate(new \DateTime());
        } else {
            // Si le produit n'est pas dans le panier, créez un nouveau ContenuPanier
            $contenuPanier = new ContenuPanier();
            $contenuPanier->setPanier($panier);
            $contenuPanier->setDate(new \DateTime());
            $contenuPanier->setProduit($produit);
            $contenuPanier->setQuantite(1);
            $entityManager->persist($contenuPanier);
        }

        // Enregistrez les changements
        $entityManager->flush();

        return $this->redirectToRoute('app_panier_show', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/remove/{id}', name: 'app_panier_remove')]
    public function remove(String $id, EntityManagerInterface $entityManager): Response
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
                    // Si la quantité est supérieure à 1, décrémentez la quantité
                    if ($contenuPanier->getQuantite() > 1) {
                        $contenuPanier->setQuantite($contenuPanier->getQuantite() - 1);
                    } else {
                        // Sinon, supprimez le produit du contenu du panier
                        $entityManager->remove($contenuPanier);
                    }

                    // Enregistrez les changements
                    $entityManager->flush();

                    break; // Sortez de la boucle une fois que le produit est trouvé
                }
            }
        }

        // Redirigez vers la page du panier après la suppression
        return $this->redirectToRoute('app_panier_show', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}', name: 'app_panier_delete', methods: ['POST'])]
    public function delete(Request $request, Panier $panier, EntityManagerInterface $entityManager): Response
    {
        // We delete the basket of the connected user
        if ($this->isCsrfTokenValid('delete'.$panier->getId(), $request->request->get('_token'))) {
            $entityManager->remove($panier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
    }
}
