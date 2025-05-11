<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
final class PageController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/', name: 'app_homepage', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/index.html.twig');
    }
}
