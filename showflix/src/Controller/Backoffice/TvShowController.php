<?php

namespace App\Controller\Backoffice;

use App\Entity\TvShow;
use App\Form\TvShowType;
use App\Repository\TvShowRepository;
use App\Service\ImageUploader;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/backoffice/tvshow",name="backoffice_tv_show_", requirements={"id": "\d+"})
 */
class TvShowController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(TvShowRepository $tvShowRepository): Response
    {
        return $this->render('backoffice/tv_show/index.html.twig', [
            'tv_shows' => $tvShowRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    public function new(Request $request, ImageUploader $imageUploader): Response
    {
        $tvShow = new TvShow();
        $form = $this->createForm(TvShowType::class, $tvShow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ------------ Upload image --------------
            // On effectue l'upload de l'image grâce au service ImageUploader et à la méthode ImageUploader:upload
            $newImageFileName = $imageUploader->upload($form, 'imageTvShow');

            // Si une nouvelle image a été uploadée
            if ($newImageFileName) {
                // On met à jour la propriété image de l'entité TvShow
                $tvShow->setImage($newImageFileName);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($tvShow);
            $entityManager->flush();

            return $this->redirectToRoute('backoffice_tv_show_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/tv_show/new.html.twig', [
            'tv_show' => $tvShow,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     *@Route("/{slug}/details", name="show_slug")

     */
    public function show(TvShow $tvShow): Response
    {
        return $this->render('backoffice/tv_show/show.html.twig', [
            'tv_show' => $tvShow,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, TvShow $tvShow, SluggerInterface $slugger, ImageUploader $imageUploader): Response
    {
        $form = $this->createForm(TvShowType::class, $tvShow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ------------ Upload image --------------
            // On effectue l'upload de l'image grâce au service ImageUploader et à la méthode ImageUploader:upload
            $newImageFileName = $imageUploader->upload($form, 'imageTvShow');

            // Si une nouvelle image a été uploadée
            if ($newImageFileName) {
                // On met à jour la propriété image de l'entité TvShow
                $tvShow->setImage($newImageFileName);
            }

            // ------------ Slug ---------------------
            // On récupère le title de la série
            // pour le transformer en slug
            $slug = $slugger->slug(strtolower($tvShow->getTitle()));
            // dd($tvShow->getTitle(), $slug);

            // On met à jour l'entité
            $tvShow->setSlug($slug);

            $tvShow->setUpdatedAt(( new DateTimeImmutable()));
            $this->getDoctrine()->getManager()->flush();

            // Message flash
            $this->addFlash('success', 'La série ' . $tvShow->getTitle() . ' a bien été mise à jour');

            return $this->redirectToRoute('backoffice_tv_show_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backoffice/tv_show/edit.html.twig', [
            'tv_show' => $tvShow,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"POST"})
     */
    public function delete(Request $request, TvShow $tvShow): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tvShow->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($tvShow);
            $entityManager->flush();

            // Message flash
            $this->addFlash('success', 'La série ' . $tvShow->getTitle() . ' a bien été supprimée');

        }

        return $this->redirectToRoute('backoffice_tv_show_index', [], Response::HTTP_SEE_OTHER);
    }
}