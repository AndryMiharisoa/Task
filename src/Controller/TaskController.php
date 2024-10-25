<?php

namespace App\Controller;

use App\Entity\Todo;
use App\Form\TodoType;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\TodoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TaskController extends AbstractController
{
    #[Route('/task', name: 'app_task')]
    public function index(TodoRepository $todoRepository): Response
    {
        return $this->render('task/index.html.twig', [
            'todos' => $todoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'todo_new', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $todo = new Todo();
        $form = $this->createForm(TodoType::class, $todo);
        $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $todo->setCreatedAt(new \DateTime()); // Set the current date and time
                $entityManager->persist($todo);
                $entityManager->flush();

                return $this->redirectToRoute('app_task', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('task/add.html.twig', [
            'todo' => $todo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'todo_edit', methods: ['GET', 'POST'])]
    public function edite(Request $request, Todo $todo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TodoType::class, $todo);
        $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Le Todo a été mis à jour avec succès.');

                return $this->redirectToRoute('app_task');
            }
            return $this->render('task/edite.html.twig', [ // Assurez-vous que le fichier Twig est correct
                'todo' => $todo,
                'form' => $form->createView(),
            ]);
}

    #[Route('/{id}', name: 'todo_delete', methods: ['POST'])]
    public function delete(Request $request, Todo $todo, EntityManagerInterface $entityManager): RedirectResponse
    {
            if ($this->isCsrfTokenValid('delete'.$todo->getId(), $request->request->get('_token'))) {
                $entityManager->remove($todo);
                $entityManager->flush();

                $this->addFlash('success', 'Le Todo a été supprimé avec succès.');
            }

    return $this->redirectToRoute('app_task');
    }
}