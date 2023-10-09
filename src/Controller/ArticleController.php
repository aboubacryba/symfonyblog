<?php
// src/Controller/ArticleController.php

namespace App\Controller;
use App\Entity\Article; // Assurez-vous d'importer la classe Article
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ArticleRepository; 
use Doctrine\ORM\EntityManagerInterface;
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="article_list")
     */
    public function list(ArticleRepository $articleRepository)
    {
        // Récupérez la liste des articles depuis la base de données
        $articles = $articleRepository->findAll();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/article/{id}", name="article_show")
     */
    public function show(Article $article)
    {
        // La variable $article est automatiquement injectée grâce à l'annotation
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/new", name="article_new")
     */
    public function new(Request $request, EntityManagerInterface $entityManager)
    {
        $article = new Article();

        // Créez un formulaire en utilisant ArticleType
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérez le téléchargement de l'image
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                // Générez un nom de fichier unique
                $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

                // Déplacez le fichier vers le répertoire de téléchargement configuré
                $imageFile->move(
                    $this->getParameter('upload_directory'),
                    $newFilename
                );

                // Mettez à jour le champ image de l'entité Article
                $article->setImage($newFilename);
            }

            // Enregistrez l'article dans la base de données
            $entityManager->persist($article);
            $entityManager->flush();

            // Redirigez vers la page de détails de l'article nouvellement créé
            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
