<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Theme;
use App\Form\AjoutThemeType;
use App\Form\ModifThemeType;

class StaticController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(){
        return $this->render('static/index.html.twig', [
            'controller_name' => 'StaticController',
        ]);
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request, \Swift_Mailer $mailer){
        $form = $this->createForm(ContactType::class);

        if($request->isMethod('POST')){
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $this->addFlash('notice','Message envoyé !');
                
                // Envoi de l'email
                $message = (new \Swift_Message($form->get('subject')->getData()))
                    ->setFrom($form->get('email')->getData())
                    ->setTo('morgan27092001@gmail.com')
                    ->setBody($this->renderView('email/emails.html.twig', array('name'=>$form->get('name')->getData(), 'subject'=>$form->get('subject')->getData(), 'message'=>$form->get('message')->getData())), 'text/html');

                $mailer->send($message);
                return $this->redirectToRoute('contact');
            }
        }

        return $this->render('static/contact.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/ajout_theme", name="ajout_theme")
     */
    public function ajoutTheme(Request $request)
    {
        $theme = new Theme();

        $form = $this->createForm(AjoutThemeType::class,$theme);

        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);            
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($theme);
                $em->flush();    
                $this->addFlash('notice', 'Thème inséré');

            }
            return $this->redirectToRoute('ajoutTheme');
        }        

        return $this->render('static/ajouttheme.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/liste_themes", name="liste_themes")
     */
    public function listeThemes(Request $request)
    {
        $em = $this->getDoctrine();
        $repoTheme = $em->getRepository(Theme::class);   

        $themes = $repoTheme->findBy(array(),array('libelle'=>'ASC'));
        return $this->render('theme/liste_themes.html.twig', [
           'themes'=>$themes
        ]);
    }


    /**
     * @Route("/modif_theme/{id}", name="modif_theme", requirements={"id"="\d+"})
     */
    public function modifThemes(int $id, Request $request)
    {
        $em = $this->getDoctrine();
        $repoTheme = $em->getRepository(Theme::class);
        $theme = $repoTheme->find($id);

        if($theme==null){
            $this->addFlash('notice', "Ce thème n'existe pas");
            return $this->redirectToRoute('listeThemes');
        }

        $form = $this->createForm(ModifThemeType::class,$theme);

        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);            
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($theme);
                $em->flush();    

                $this->addFlash('notice', 'Thème modifié');

            }
            return $this->redirectToRoute('listeThemes');
        }        

        return $this->render('theme/modif_theme.html.twig', [
            'form'=>$form->createView()
        ]);
    }
}
