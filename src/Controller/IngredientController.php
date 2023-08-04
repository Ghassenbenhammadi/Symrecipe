<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class IngredientController extends AbstractController
{
    #[Route('/ingredient', name: 'app_ingredient', methods:['GET'])]
    public function index(
    IngredientRepository $ingredientRepository,
    PaginatorInterface $paginator,
    Request $request
    
    ): Response
    {

      $ingredients = $paginator->paginate(
        $ingredientRepository->findBy(['user' => $this->getUser()]), /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        10 /*limit per page*/
    );
        return $this->render('pages/ingredient/index.html.twig', [
          'ingredients' => $ingredients
        ]);
    }

    #[Route('/ingredient/nouveau', name: 'ingredient.new', methods:['GET','POST'])]
    public function new(
    EntityManagerInterface $manager,
    Request $request): Response
    {
      $ingredient = new Ingredient();
      $form = $this->createForm(IngredientType::class, $ingredient);

      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
         $ingredient= $form->getData();
         $ingredient->setUser($this->getUser());

         $manager->persist($ingredient);
         $manager->flush();
         $this->addFlash('success','Votre ingrédient a été créé avec succés !');
         return $this->redirectToRoute('app_ingredient');
      }
     // else{}
        return $this->render('pages/ingredient/new.html.twig',[
          'form'=> $form->createView()
        ]);
    }

    #[Route('/ingredient/edition/{id}', name: 'ingredient.edit', methods:['GET','POST'])]
    public function edit(
       Ingredient $ingredient,
       Request $request,
       EntityManagerInterface $manager
       ): Response

    {
      
      $form = $this->createForm(IngredientType::class, $ingredient);
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
         $ingredient= $form->getData();
         $manager->persist($ingredient);
         $manager->flush();
         $this->addFlash('success','Votre ingrédient a été modifié avec succés !');
         return $this->redirectToRoute('app_ingredient');
      }  
      return $this->render('pages/ingredient/edit.html.twig',[
          'form' => $form->createView()
        ]);
    }
#[Route('/ingredient/suppression/{id}', 'ingredient.delete', methods: ['GET'])]
  public function delete(
    EntityManagerInterface $manager,
    Ingredient $ingredient
    ): Response
  {
    if (!$ingredient) {
      $this->addFlash('success','Ingredient n\'exixte pas');
      return $this->redirectToRoute('app_ingredient');
    }
    $manager->remove($ingredient);
    $manager->flush();
    $this->addFlash('success','Votre ingrédient a été supprimé avec succés !');
    return $this->redirectToRoute('app_ingredient');
  }

    
    
}
