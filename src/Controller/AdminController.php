<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
class AdminController extends AbstractController
{
    // Simple route pour dashboard admin
    #[Route('/{_locale}/admin', name: 'app_admin')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        return $this->render('admin/index.html.twig');
    }
}
