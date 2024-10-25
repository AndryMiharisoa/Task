<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{   

    #[Route('/users', name: 'app_user_list')]
    public function index(EntityManagerInterface $entityManager): Response
        {   
            $users = $entityManager->getRepository(User::class)->findAll();
                 return $this->render('security/index.html.twig', [
                   'users' => $users,
            ]);
        }

        #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Obtenir l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        // Dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
        #[Route('/user/edit/{id}', name: 'app_edit_user')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
                if (!$user) {
                    throw $this->createNotFoundException('User not found');
                }

                    $form = $this->createFormBuilder($user)
                        ->add('email', EmailType::class, ['label' => 'Email'])
                        ->add('password', SubmitType::class, ['label' => 'mot_de_passe'])
                        ->getForm();
                           $form->handleRequest($request);

                            if ($form->isSubmitted() && $form->isValid()) {
                                $entityManager->flush(); // Enregistrer les modifications

                                return $this->redirectToRoute('app_user_list'); // Redirection vers la liste des utilisateurs
                            }

                    return $this->render('security/edit.html.twig', [
                        'form' => $form->createView(),
                        'user' => $user
                    ]);
    }

    #[Route('/user/delete/{id}', name: 'app_user_delete')]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur par son ID
        $user = $entityManager->getRepository(User::class)->find($id);

        // Vérifier si l'utilisateur existe
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Vérifiez que la requête est une soumission POST
        if ($request->isMethod('POST')) {
            // Supprimer l'utilisateur de la base de données
            $entityManager->remove($user);
            $entityManager->flush();

            // Rediriger vers la liste des utilisateurs
            return $this->redirectToRoute('app_user_list');
        }

        // Rendre le template de confirmation de suppression
        return $this->render('security/delete.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
        {
           throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
        }
}    
