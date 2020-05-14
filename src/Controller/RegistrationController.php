<?php

namespace App\Controller;

use App\Entity\Uploads;
use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Form\UploadType;
use App\Repository\UsersRepository;
use App\Security\UsersAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use App\Notifications\CreationCompteNotification;
use App\Notifications\ActivationCompteNotification;


class RegistrationController extends AbstractController
{
    /**
     * @var CreationCompteNotification
     */
    private $notify_creation;

    /**
     * @var ActivationCompteNotification
     */
    private $notify_activation;

    public function __construct(CreationCompteNotification $notify_creation, ActivationCompteNotification $notify_activation)
    {
        $this->notify_creation = $notify_creation;
        $this->notify_activation = $notify_activation;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, UsersAuthenticator $authenticator, \Swift_Mailer $mailer): Response
    {
        $upload = new Uploads();
        $formUpload = $this->createForm(UploadType::class);
        if ($formUpload->isSubmitted() && $formUpload->isValid()) {
           $file = $upload->getName();
           $fileName = md5(uniqid()).'.'.$file->guessExtension();
           $file->move($this->getParameter('app.path.featured_images'), $fileName);
           $upload->setName($fileName);
        }

        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            //on genere le TOKEN d'activation
            $user->setActivationToken(md5(uniqid()));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            //envoie de notification admin
            $this->notify_creation->notify();
            $this->notify_activation->notify($user);

            $message = (new \Swift_Message('activation de votre compte'))
                ->setFrom('t.datchy@laposte.net')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'email/activation.html.twig', ['token'=> $user->getActivationToken()]
                    ),
                    'text/html'
                )
            ;
            $mailer->send($message);

            
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'formUpload' => $formUpload->createView(), 
        ]);
    }

    /**
     * @Route("/activation/{token}", name="activation")
     */
    public function activation($token, UsersRepository $usersRepo ){

        $user= $usersRepo->findOneBy(['activation_token' => $token]); 

        if (!$user) {
            //erreur 404
            throw $this->createNotFoundException("Error, cet utilisateur n'existe pas");
             
        }

        $user->setActivationToken(null);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        //on envoie un flash
        $this->addFlash('message', 'Vous avez bien activé votre compte.');
        return $this->redirectToRoute('accueil');
    }
}
