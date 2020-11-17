<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Theme;
use App\Entity\Fichier;
use App\Entity\Utilisateur;
use App\Form\AjoutThemeType;
use App\Form\ModifThemeType;
use App\Form\AjoutUtilisateurType;
use App\Form\ModifUtilisateurType;
use App\Form\ImageProfilType;


class UtilisateurController extends AbstractController
{
    /**
     * @Route("/utilisateur", name="utilisateur")
     */
    public function index()
    {
        return $this->render('utilisateur/index.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    /**
     * @Route("/liste_utilisateurs", name="liste_utilisateurs")
     */
    public function listeUtilisateurs(Request $request)
    {
        $em = $this->getDoctrine();
        $repoUtilisateur = $em->getRepository(Utilisateur::class);   

        if ($request->get('supp')!=null){
            $utilisateur = $repoUtilisateur->find($request->get('supp'));
            if($utilisateur!=null){
                $em->getManager()->remove($utilisateur);
                $em->getManager()->flush();
            }    
            return $this->redirectToRoute('listeUtilisateurs');
        }

        $utilisateurs = $repoUtilisateur->findBy(array(),array('nom'=>'ASC'));
        return $this->render('utilisateur/liste_utilisateurs.html.twig', [
           'utilisateur'=>$utilisateurs
        ]);
    }

    /**
     * @Route("/ajout_utilisateur", name="ajout_utilisateur")
     */
    public function ajoutUtilisateur(Request $request)
    {
        $utilisateur = new Utilisateur();

        $form = $this->createForm(AjoutUtilisateurType::class, $utilisateur);

        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);            
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($utilisateur);
                $em->flush();    
                $this->addFlash('notice', 'Utilisateur inséré');

            }
            return $this->redirectToRoute('ajoutUtilisateur');
        }        

        return $this->render('utilisateur/ajout_utilisateur.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/profil_utilisateur/{id}", name="profil_utilisateur", requirements={"id"="\d+"})
     */
    public function profilUtilisateur(int $id, Request $request)
    {
        $em = $this->getDoctrine();
        $repoUtilisateur = $em->getRepository(Utilisateur::class);
        $utilisateur = $repoUtilisateur->find($id);

        $form=$this->createForm(ModifThemeType::class);

        $path = $this->getPArameter('profile_directory').'/profil1.png';
        $data=file_get_contents($path);
        $base64 ='data:image/png;base64,'.base64_encode($data);

        if ($request->get('supp')!=null){
            $utilisateur = $repoUtilisateur->find($request->get('supp'));
            if($utilisateur!=null){
                $em->getManager()->remove($utilisateur);
                $em->getManager()->flush();
            }    
            return $this->redirectToRoute('listeUtilisateurs');
        }
        
            

    return $this->render('utilisateur/profil_utilisateur.html.twig', [
        'utilisateur'=>$utilisateur,
        'form'=>$form->createView(),
        'base64'=> $base64
    ]);
    }

    /**
     * @Route("/modif_utilisateur/{id}", name="modif_utilisateur", requirements={"id"="\d+"})
     */
    public function modifUtilisateur(int $id, Request $request)
    {
        $em = $this->getDoctrine();
        $repoUtilisateur = $em->getRepository(Utilisateur::class);
        $utilisateur = $repoUtilisateur->find($id);

        $form = $this->createForm(ModifUtilisateurType::class,$utilisateur);

        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);            
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($utilisateur);
                $em->flush();    

                $this->addFlash('notice', 'Utilisateur modifié');

            }
            return $this->redirectToRoute('listeUtilisateurs');
        }        

        return $this->render('utilisateur/modif_utilisateur.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/user_profile/{id}", name="user_profile", requirements={"id"="\d+"})
     */
    public function userprofile(int $id, Request $request)
    {
     
        $em = $this->getDoctrine();
        $repoUtilisateur = $em->getRepository(Utilisateur::class);
        $utilisateur = $repoUtilisateur->find($id);
        if ($utilisateur==null){
            $this->addFlash('notice','Utilisateur introuvable');
            return $this->redirectToRoute('accueil');
        }
        $form = $this->createForm(ImageProfilType::class);
        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);            
            if ($form->isSubmitted() && $form->isValid()) {
                $file = $form->get('photo')->getData();
                try{    
                    $fileName = $utilisateur->getId().'.'.$file->guessExtension();
                    $file->move($this->getParameter('profile_directory'),$fileName); // Nous déplaçons lefichier dans le répertoire configuré dans services.yaml
                    $em = $em->getManager();
                    $utilisateur->setPhoto($fileName);
                    $em->persist($utilisateur);
                    $em->flush();
                    $this->addFlash('notice', 'Fichier inséré');

                } catch (FileException $e) {                // erreur durant l’upload            }
                    $this->addFlash('notice', 'Problème fichier inséré');
                }
            }
        }    

        if($utilisateur->getPhoto()==null){
            $path = $this->getParameter('profile_directory').'/defaut.png';
        }
        else{
            $path = $this->getParameter('profile_directory').'/'.$utilisateur->getPhoto();
        }    
        $data = file_get_contents($path);
        $base64 = 'data:image/png;base64,' . base64_encode($data);

        return $this->render('utilisateur/profil_utilisateur.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
            'base64' => $base64
        ]);
    }    
}