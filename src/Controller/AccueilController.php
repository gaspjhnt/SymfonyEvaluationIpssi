<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    //Route principale
    #[Route('/{_locale}/', name: 'app_accueil')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupération pour affichage de tous les produits
        $produits = $em->getRepository(Produit::class)->findAll();
        return $this->render('accueil/index.html.twig', [
            'produits' => $produits,
        ]);
    }
}
