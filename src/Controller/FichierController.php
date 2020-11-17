<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AjoutFichierType;
use App\Entity\Fichier;


class FichierController extends AbstractController
{
    /**
     * @Route("/fichier", name="fichier")
     */
    public function index()
    {
        return $this->render('fichier/index.html.twig', [
            'controller_name' => 'FichierController',
        ]);
    }
        /**
         * @Route("/ajout_fichier", name="ajout_fichier")
         */
        public function ajoutFichier()
        {
            $fichier = new Fichier();
        $form = $this->createForm(AjoutFichierType::class,$fichier);

        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);            
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $file = $fichier->getNom();
                $fichier->setDate(new \DateTime()); //récupère la date du jour
                $fichier->setExtension($file->guessExtension()); // Récupère l’extension du fichier
                $fichier->setTaille($file->getSize()); // getSize contient la taille du fichier envoyé
             
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                $fichier->setNom($fileName);
                $em->persist($fichier);
                $em->flush();    
                try{    
                    $file->move($this->getParameter('file_directory'),$fileName); // Nous déplaçons lefichier dans le répertoire configuré dans services.yaml
                    $this->addFlash('notice', 'Fichier inséré');

                } catch (FileException $e) {                // erreur durant l’upload            }
                    $this->addFlash('notice', 'Problème fichier inséré');
                }
            return $this->redirectToRoute('ajout_fichier');
        }        
    }

        return $this->render('fichier/ajout_fichier.html.twig', [
           'form'=>$form->createView()
        ]);
    }
        
        /**     
         * * @return string     
         * 
         * */    
        private function generateUniqueFileName()    
        {        
            return md5(uniqid());    
        }

         /**
     * @Route("/telechargement_fichier/{id}", name="telechargement_fichier", requirements={"id"="\d+"})
     */
    public function telechargementFichier(int $id)
    {
      $em = $this->getDoctrine();
      $repoFichier = $em->getRepository(Fichier::class);  
      $fichier = $repoFichier->find($id);
      if ($fichier == null){
        $this->redirectToRoute('liste_fichiers');
      }
      else{
        return $this->file($this->getParameter('file_directory').'/'.$fichier->getNom());  
      
      }
    }

}
