<?php

namespace App\Controller;

use App\Entity\Articles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AddArticleFormType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\User\User;

class ArticlesController extends AbstractController
{
    /**
     * @Route("/articles", name="articles")
     */
    public function index()
    {
        $articles = $this->getDoctrine()->getRepository(Articles::class)->findAll();
        
        return $this->render('articles/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @IsGranted("ROLE_MODO")
     * @Route("/article/add", name="add_article")
     */
    public function AddArticle(Request $request, TranslatorInterface $translator)
    {
        $article = new Articles();

        $form = $this->createForm(AddArticleFormType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUsers($this->getUser());
            $doctrine = $this->getDoctrine()->getManager();
            $doctrine->persist($article);
            $doctrine->flush();

            $message = $translator->trans('Article published successfully');

            $this->addFlash('message', $message);
            return $this->redirectToRoute('articles');
        }

        return $this->render('articles/ajout.html.twig', [
            'articleForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/{slug}", name="article")
     */
    public function article($slug)
    {
        $article = $this->getDoctrine()->getRepository(Articles::class)->findOneBy([
            'slug' => $slug,
        ]);

        if (!$article) {
            throw $this->createNotFoundException("L'article n'existe pas");
        }

        return $this->render('articles/article.html.twig', [
            'article' => $article
        ]);
    }

}
