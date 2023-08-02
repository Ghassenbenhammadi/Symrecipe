<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    #[Route('/recette', name: 'app_recipe', methods: ['GET'])]
    public function index(
    RecipeRepository $recipeRepository,
    PaginatorInterface $paginator,
    Request $request): Response
    {
        $recipes = $paginator->paginate(
            $recipeRepository->findAll(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        return $this->render('pages/recipe/index.html.twig', [
            'controller_name' => 'RecipeController',
            'recipes'         => $recipes,
        ]);
    }

    #[Route('/recette/nouveau', name: 'recipe.new', methods:['GET','POST'])]
    public function new(
    // EntityManagerInterface $manager,
    // Request $request
    ): Response
    {
      $recipe = new Recipe();
      $form = $this->createForm(RecipeType::class, $recipe);

    //   $form->handleRequest($request);
    //   if ($form->isSubmitted() && $form->isValid()) {
    //      $recipe= $form->getData();
    //      $manager->persist($recipe);
    //      $manager->flush();
    //      $this->addFlash('success','Votre recette a été créé avec succés !');
    //      return $this->redirectToRoute('app_recipe');
    //   }
     // else{}
        return $this->render('pages/recipe/new.html.twig',
        [
           'form'=> $form->createView()
        ]
    );
    }
}
