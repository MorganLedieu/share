<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


     /**
 * @Route("/inscrire", name="inscrire")
 */
 public function inscrire(Request $request, UserPasswordEncoderInterface $passwordEncoder){
    $user = new User();
    $form = $this->createFormBuilder($user)
    ->add('username', TextType::class)
    ->add('password', PasswordType::class)
    ->add('save', SubmitType::class, array('label' => 'S\'inscrire'))
    ->getForm();
   
    if ($request->isMethod('POST')){
    $form -> handleRequest($request);
    if($form->isValid()){
    $em = $this->getDoctrine()->getManager();
    $user->setRoles(array('ROLE_USER'));
   
    $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));
    $em->persist($user);

    $em->flush();
 return $this->redirectToRoute('listeThemes');
 }
 }

 return $this->render('security/inscrire.html.twig', ['form' => $form->createView()]);
   
}
}

