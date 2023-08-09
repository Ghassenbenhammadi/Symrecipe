<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(
        Request $request,
        EntityManagerInterface $manager,
        MailerInterface $mailer
        ): Response
    {
        $contact = new Contact();

        // if ($this->getUser()) {
        //     $contact->setFullName($this->getUser()->getFullName())
        //             ->setEmail($this->getUser()->getEmail());
        // }
        $form = $this->createForm(ContactType::class, $contact);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();

            $manager->persist($contact);
            $manager->flush();

    //Email
    $email = (new Email())
    ->from('hello@example.com')
    ->to('you@example.com')
    ->subject('Time for Symfony Mailer!')
    ->text('Sending emails is fun again!')
    ->html('<p>See Twig integration for better HTML integration!</p>');

$mailer->send($email);


            $this->addFlash('success','Votre demande a été envoyé avec succés !');
            return $this->redirectToRoute('app_contact');
        }
        return $this->render('pages/contact/index.html.twig', [
            'form'=>$form->createView(),
        ]);
    }
}
