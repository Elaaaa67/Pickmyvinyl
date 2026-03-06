<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LoginSuccessController extends AbstractController
{
    #[Route('/login-success', name: 'app_login_success')]
    public function index(): Response
    {
        return $this->render('security/login_success.html.twig');
    }
}

