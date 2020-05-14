<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="accueil")
     */
    public function index()
    {
        return $this->render('main/index.html.twig');
    }

    /**
     * @Route("/mention", name="mentions")
     */
    public function mentions()
    {

        return $this->render('main/mentions.html.twig');
    }

    /**
     * @Route("/sites_amis", name="amis")
     */
    public function amis()
    {
        return $this->render('main/amis.html.twig');
    }

    /**
     * @Route("/RGPD" , name="rgpd")
     */
    public function rgpd()
    {
        return $this->render('main/rgpd.html.twig');
    }

    /**
     * @Route("/change_locale/{locale}", name="change_locale")
     */
    public function changeLocale($locale, Request $request)
    {
        //$request->setLocale($locale);
        $request->getSession()->set('_locale', $locale);
        return $this->redirect($request->headers->get('referer'));
    }
}   
