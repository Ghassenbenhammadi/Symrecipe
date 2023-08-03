<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/utilisateurs/edition/{id}', name: 'user.edit', methods: ['GET','POST'])]
    public function edit(
        User $user, 
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if($this->getUser() !== $user) {
            return $this->redirectToRoute('app_recipe');
        }
        $form = $this->createForm(UserType::class, $user);
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($user,$form->getData()->getPlainPassword()))
            {
                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'modification effecuté avec succés');
                return $this->redirectToRoute('app_recipe');
            } else {
                $this->addFlash('warning', 'le mot de passe est incorrect');
            }
        }
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/utilisateurs/edition-mot-de-passe/{id}', name: 'user.edit.password', methods: ['GET','POST'])]
    public function editPassword(
        User $user, 
        Request $request,
        EntityManagerInterface  $manager,
        UserPasswordHasherInterface $hasher
        ) : Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_recipe');
        }
        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('app_recipe');
        }
        $form = $this->createForm(UserPasswordType::class);
       $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid()){
        if($hasher->isPasswordValid($user , $form->getData()['plainPassword']))
        {
            $user->setUpdatedAt(new \DateTimeImmutable());
            $user->setPlainPassword( $form->getData()['newPassword']);
            
            $manager->persist($user);
            $manager->flush();
            
            $this->addFlash('success','mot de passe a été modifié');
             return $this->redirectToRoute('app_recipe');  
        } else {
            $this->addFlash('warning', 'le mot de passe est incorrect');
        }
       }
        return $this->render('pages/user/edit_password.html.twig',[
            'form'=> $form->createView()
        ]);
    }
}
