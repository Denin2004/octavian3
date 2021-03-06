<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Mobile_Detect;

use App\Services\SiteConfig\SiteConfig;

class Application extends AbstractController
{
    /**
     * @Route("{reactRouting}", name="default", defaults={"reactRouting": null})
     */
    public function index(SiteConfig $config)
    {
        $detect = new Mobile_Detect;
        if ($detect->isMobile()) {
            return $this->render(
                'base.mobile.html.twig',
                [
                    'numeral' => $config->get('numeral')
                ]
            );
        }
        return $this->render(
            'base.web.html.twig',
            [
                'numeral' => $config->get('numeral')
            ]
        );
    }

    public function error403(Request $request)
    {
        return new Response('saaaaa');
    }

    public function config()
    {
        $res = [
            'success' => true,
            'urls' => $this->renderView('urls.json.twig'),
            'user' => [
                'name' => $this->getUser()->getUsername(),
                'id' => $this->getUser()->getUserId(),
            ]
        ];
        return new JsonResponse($res);
    }
}
