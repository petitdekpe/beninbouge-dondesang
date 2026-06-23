<?php

namespace App\Controller;

use App\Repository\BirthdayMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminMessageController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/admin/messages', name: 'admin_messages', methods: ['GET'])]
    public function index(BirthdayMessageRepository $messages): Response
    {
        return $this->render('admin/messages.html.twig', [
            'messages' => $messages->findAllForAdmin(),
        ]);
    }

    #[Route('/admin/messages/{id}/toggle', name: 'admin_messages_toggle', methods: ['POST'])]
    public function toggle(int $id, Request $request, BirthdayMessageRepository $messages): RedirectResponse
    {
        $message = $messages->find($id);
        if ($message && $this->isCsrfTokenValid('admin_message_' . $id, (string) $request->request->get('_token'))) {
            $message->setVisible(!$message->isVisible());
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_messages');
    }

    #[Route('/admin/messages/{id}/delete', name: 'admin_messages_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, BirthdayMessageRepository $messages): RedirectResponse
    {
        $message = $messages->find($id);
        if ($message && $this->isCsrfTokenValid('admin_message_' . $id, (string) $request->request->get('_token'))) {
            $this->em->remove($message);
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_messages');
    }
}
