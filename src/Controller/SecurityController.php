<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\ResetPassType;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/forgot-pass", name="app_forgotten_password")
     */
    public function forgotPass(Request $request, UsersRepository $usersRepo, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        //on crée le formulaire
        $form = $this->createForm(ResetPassType::class);

        //on fait le traitement
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user = $usersRepo->findOneByEmail($data['email']);

            if (!$user) {
                $this->addFlash('danger', 'cette adresse n\'existe pas' );

                $this->redirectToRoute('app_login');
            }

            //on genère le token
            $token = $tokenGenerator->generateToken();

            try {
                $user->setResetToken($token);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            } catch (\Throwable $e) {
                $this->addFlash('warning', 'une erreur est survenue : '. $e->getMessage());
                return $this->redirectToRoute('app_login');
            }

            //on genere l'url de reinitialisation de mdp
            $url = $this->generateUrl('app_reset_password', ['token'=> $token], UrlGeneratorInterface::ABSOLUTE_URL);

            //on envoie le mail
            $message= (new \Swift_Message('mot de passe oublié'))
                ->setFrom('t.datchy@laposte.net')
                ->setTo($user->getEmail)
                ->setBody(
                    "<p>Bonjour,</p><p>Une demande de réinitialisation de mot de passe a été effectuée pour le site FuturTransmission. Veuillez cliquer sur le lien suivant : ". $url ."</p>", "text/html"
                )
            ;

            $mailer->send($message);

            $this->addFlash('message', 'Un e-mail de réinitialisation de mot de passe vous a été envoyé');
            $this->redirectToRoute('app_login');

        }

        //on envoie vers la page de demande de l'email
        return $this->render('security/forgottenPassword.html.twig', ['emailForm' => $form->createView()]);
    }


    /**
     * @Route("/reset-pass/{token}", name="app_reset_password")
     */
    public function resetPassword($token, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        //on va chercher l'utilisateur avec le token fourni
        $user = $this->getDoctrine()->getRepository(Users::class)->findOneBy(['reset_token' => $token]);

        if (!$user) {
            $this->addFlash('danger', 'token inconnu');
            return $this->redirectToRoute('app_login');
        }

        //on verifie si le form est envoyé en methode POST
        if ($request->isMethod('POST')) {
            //on supprime le token 
            $user->setResetToken(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('message', 'mot de passe modifié avec succès');
            return $this->redirectToRoute('app_login');
        }else {
            return $this->render('security/reset-password.html.twig', ['token' => $token]);
        }
    }
}
