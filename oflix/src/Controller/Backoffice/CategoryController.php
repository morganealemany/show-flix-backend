<?php

namespace App\Controller\Backoffice;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backoffice/category", name="backoffice_category_")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(CategoryRepository $repositoryCategory): Response
    {
        return $this->render('backoffice/category/index.html.twig', [
            'categories' => $repositoryCategory->findAll(),
        ]);
    }

    /**
     * Permet de créer une nouvelle catégorie
     * 
     * @Route("/add", name="add")
     *
     * @return void
     */
    public function add(Request $request) 
    {
        // 1) On instancie un objet vide 
        $category = new Category();

        // 2) On instancie le formtype et on le lie à notre instance $category vide
        $form = $this->createForm(CategoryType::class, $category);

        // 4) On récupére les données issues du formulaire et on les injecte dans l'objet $category
        $form->handleRequest($request);

        // 5) On vérifie qu'on est bien dans le cas de la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) 
        {
            // On créé la nouvelle catégorie
            // en appelant le manager de doctrine
            $em = $this->getDoctrine()->getManager();
            // on fait un "commit" de notre nouvelle catégorie
            $em->persist($category);

            // puis on fait un "push" pour la sauvegarder en BDD
            $em->flush();

            // On ajoute un message flash pour l'UX
            $this->addFlash('success', 'La catégorie ' . $category->getName() .' a bien été créée');

            // On redirige la page vers l'index des catégories
            return $this->redirectToRoute('backoffice_category_index');
        }

        // 3) On retourne le formulaire pour qu'il puisse s'afficher dans la vue
        return $this->render('backoffice/category/add.html.twig', [
            'formView' => $form->createView(),
        ]);
    }

    /**
     * Permet d'éditer une catégorie
     * 
     * @Route("/{id}/edit", name="edit", requirements= {"id": "\d+"})
     *
     * @param integer $id
     * 
     * @return void
     */
    public function edit(int $id, Request $request, CategoryRepository $repositoryCategory)
    {
        // 1) On récupére l'id de la catégorie à modifier
        $category = $repositoryCategory->find($id);

        // 2) On instancie le formtype et on lie l'instance $category à notre formulaire
        $form = $this->createForm(CategoryType::class, $category);

        // 4) On réceptionne les données issues du formulaire et on les injecte dans l'objet $category
        $form->handleRequest($request);

        // 5) On vérifie qu'on est bien dans le cas d'une soumission de formulaire
        if ($form->isSubmitted()) {
            // On met à jour la catégorie
            // en appelant le manager de doctrine
            $em = $this->getDoctrine()->getManager();
            // persist n'est pas nécessaire lors d'une MAJ
            $em->flush();
        
            // Message flash
            $this->addFlash('success', 'La catégorie a bien été mise à jour');

            // Redirection sur la page de la catégorie
            return $this->redirectToRoute('backoffice_category_index', [
                'id' => $id,
            ]);
        }

        // 3) On affiche le formulaire dans la vue
        return $this->render('backoffice/category/edit.html.twig', [
            'formView' => $form->createView(),
            'category' => $category,
        ]);

    }

    /**
     * Permet la suppression d'une catégorie
     * 
     * @Route("/{id}/delete", name="delete", requirements= {"id": "\d+"})
     *
     * @param integer $id
     * @return void
     */
    public function delete(int $id, CategoryRepository $repositoryCategory)
    {
        // On récupére les infos de la catégorie dont l'id est passé en argument
        $category = $repositoryCategory->find($id);
        
        // On fait appel au manager de doctrine pour gérer la suppression
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        // Message flash
        $this->addFlash('success', 'La catégorie ' . $category->getName() . ' a bien été supprimée');

        // Redirection vers la page index des catégories
        return $this->redirectToRoute('backoffice_category_index');
    }
}
