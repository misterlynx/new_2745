<?php

namespace App\Notifications;

use App\Entity\Users;
use Swift_Mailer;
use Swift_Message;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ActivationCompteNotification
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
    public function notify(Users $user)
    {
        # on construit le mail
        $message = (new Swift_Message('Futur Transmission - Activation du Compte'))
            ->setFrom('')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderer->render(
                    'email/activation.html.twig',
                    ['token'=> $user->getActivationToken()]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}
