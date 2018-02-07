<?php

namespace App\Controller;

use App\Settings\Settings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AppController extends Controller
{
    /**
     * @Route("/", name="root")
     */
    public function root(): Response
    {
        $locale = 'fr';

        return $this->redirectToRoute('homepage', [
            '_locale' => $locale,
        ]);
    }

    /**
     * @Route("/{_locale}", name="homepage")
     */
    public function homepage(): Response
    {
        $settings = new Settings();

        return $this->render('base.html.twig', [
            'settings' => $settings,
        ]);
    }
}
