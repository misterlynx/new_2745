<?php

namespace App\Notifications;

use Swift_Mailer;
use Swift_Message;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CreationCompteNotification
{
    /**
     * @var \Swift_Mailer
     * 
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $renderer;

    public function __Construct(\Swift_Mailer $mailer, Environment $renderer)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    /**
     * Méthode de notification (envoi de mail)
     * 
     * @return void
     */
    public function notify()
    {
        # on construit le mail
        $message = (new Swift_Message('Mon blog - Nouvelle inscription'))
            ->setFrom('')
            ->setTo('t.datchy@laposte.net')
            ->setBody(
                $this->renderer->render(
                    'email/ajout_compte.html.twig'
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}
