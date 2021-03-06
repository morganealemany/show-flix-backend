<?php

namespace App\Controller;

use App\Repository\SeasonRepository;
use App\Repository\TvShowRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/tvshow", name="tvshow_")
 */
class TvShowController extends AbstractController
{
    /**
     * Page affichant la liste des séries
     * 
     * @Route("/", name="index")
     */
    public function index (TvShowRepository $repositoryTvShow): Response 
    {
        $list = $repositoryTvShow->findAll();

        return $this->render('tv_show/index.html.twig', [
            'list' => $list,
        ]);
    }

    /**
     * Méthode affichant les détails d'une série en fonction de son id
     * 
     * @Route("/{id}", name="show", requirements={"id": "\d+"})
     *
     * @return Response
     */
    public function show(int $id, TvShowRepository $repositoryTvShow, SeasonRepository $repositorySeason): Response
    {
        // Récupérayion des infos de la série dont l'id est passée en argument
        $tvshow = $repositoryTvShow->find($id);

        // Si la série n'existe pas on affiche une 404
        if (!$tvshow) {
            throw $this->createNotFoundException()("La série $id n'existe pas");   
        }

        return $this->render('tv_show/show.html.twig', [
            'tvshow' => $tvshow,
        ]);
    }
}
