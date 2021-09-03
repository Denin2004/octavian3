<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Mobile_Detect;

class Application extends AbstractController
{
    /**
     * @Route("{reactRouting}", name="default", defaults={"reactRouting": null})
     */
    public function index()
    {
        $detect = new Mobile_Detect;
        if ($detect->isMobile()) {
            return $this->render('base_mobile.html.twig');
        }
        return $this->render('base_web.html.twig');
    }

    public function error403(Request $request)
    {
        return new Response('saaaaa');
    }
}
