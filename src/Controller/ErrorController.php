<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    public function show(FlattenException $exception): Response
    {
        return $this->render('error/error404.html.twig', [],
            new Response('', $exception->getStatusCode()));
    }
}
