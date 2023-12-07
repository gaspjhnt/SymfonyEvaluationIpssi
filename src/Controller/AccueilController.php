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

// We retrieve all the products from the database using Doctrine and pass them to a Twig template for display.
class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(EntityManagerInterface $em): Response
    {
        // Get all the products from the database
        $produits = $em->getRepository(Produit::class)->findAll();
        return $this->render('accueil/index.html.twig', [
            'produits' => $produits,
        ]);
    }
}
