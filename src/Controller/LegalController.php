<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'mentions_legales', methods: ['GET'])]
    public function mentionsLegales(): Response
    {
        return $this->render('mentions-legales.html.twig');
    }

    #[Route('/confidentialite', name: 'confidentialite', methods: ['GET'])]
    public function confidentialite(): Response
    {
        return $this->render('confidentialite.html.twig');
    }
}
