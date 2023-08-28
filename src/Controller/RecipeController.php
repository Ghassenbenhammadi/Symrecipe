<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class RecipeController extends AbstractController
{
    #[Route('/recette', name: 'app_recipe', methods: ['GET'])]
    public function index(
    RecipeRepository $recipeRepository,
    PaginatorInterface $paginator,
    Request $request): Response
    {
        $recipes = $paginator->paginate(
            $recipeRepository->findBy(['user' => $this->getUser()]), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        return $this->render('pages/recipe/index.html.twig', [
            'controller_name' => 'RecipeController',
            'recipes'         => $recipes,
        ]);
    }


    #[Route('/recette/communaute', name: 'recipe.community', methods:['GET'])]
    public function indexPublic(
      RecipeRepository $repository,
      PaginatorInterface $paginator,
      Request $request

    ): Response
    {

      $cache = new FilesystemAdapter();
      $data = $cache->get('recipes', function(ItemInterface $item) use ($repository) {
        $item->expiresAfter(15);
        return $repository->findPublicRecipe(null);
      });
      $recipes = $paginator->paginate(
        $data, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        10 /*limit per page*/
    );
      return $this->render('pages/recipe/community.html.twig',[
      'recipes' => $recipes,
      ]);
    }

    #[Security("is_granted('ROLE_USER') and recipe.getIsPublic() === true")]
    #[Route('/recette/{id}', name: 'recipe.show', methods:['GET', 'POST'])]
    public function show( 
      Recipe $recipe,
      Request $request,
      MarkRepository $markRepository,
      EntityManagerInterface $manager):Response
    {
      $mark = new Mark();
      $form = $this->createForm(MarkType::class, $mark);
     
     $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
        $mark->setUser($this->getUser())
        ->setRecipe($recipe);

        $existMark = $markRepository->findOneBy([
          'user' => $this->getUser(),
          'recipe' => $recipe,
        ]);

        if(!$existMark){
          $manager->persist($mark);
        } else {
          $existMark->setMark(
            $form->getData()->getMark()
          );
        }
        $manager->flush();
        $this->addFlash('success','votre note a été prise en compte ');
       
      
      }
      return $this->render('pages/recipe/show.html.twig',
      [
        'recipe' => $recipe,
        'form' => $form->createView(),
      ]);
    }
    
    #[IsGranted('ROLE_USER')]
    #[Route('/recette/creation', 'recipe.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());
         
            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été créé avec succès !'
            );

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/new.html.twig', 
        [
            'form' => $form->createView(),
        ]
      );
    }

    #[Security("is_granted('ROLE_USER') and user ===recipe.getUser()")]
    #[Route('/recette/edition/{id}', name: 'recipe.edit', methods:['GET','POST'])]
    public function edit(
       Recipe $recipe,
       Request $request,
       EntityManagerInterface $manager
       ): Response

    {
      
      $form = $this->createForm(RecipeType::class, $recipe);
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
         $recipe= $form->getData();
         $manager->persist($recipe);
         $manager->flush();
         $this->addFlash('success','Votre recette a été modifié avec succés !');
         return $this->redirectToRoute('app_recipe');
      }  
      return $this->render('pages/recipe/edit.html.twig',[
          'form' => $form->createView()
        ]);
    }

    #[Route('/recette/suppression/{id}', 'recipe.delete', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER') and user ===recipe.getUser()")]
    public function delete(
    EntityManagerInterface $manager,
    Recipe $recipe
    ): Response
  {
    if (!$recipe) {
      $this->addFlash('success','Recette n\'existe pas');
      return $this->redirectToRoute('app_recipe');
    }
    $manager->remove($recipe);
    $manager->flush();
    $this->addFlash('success','Votre recette a été supprimé avec succés !');
    return $this->redirectToRoute('app_recipe');
  }
}
