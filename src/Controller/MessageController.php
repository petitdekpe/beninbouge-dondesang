<?php

namespace App\Controller;

use App\Entity\BirthdayMessage;
use App\Repository\BirthdayMessageRepository;
use App\Translation\Dictionary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MessageController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/messages', name: 'messages_create', methods: ['POST'])]
    public function create(Request $request): RedirectResponse
    {
        $lang = $request->request->get('lang') === 'en' ? 'en' : 'fr';
        $name = trim((string) $request->request->get('author_name', ''));
        $message = trim((string) $request->request->get('message', ''));

        if ($name !== '' && $message !== '') {
            $birthdayMessage = new BirthdayMessage();
            $birthdayMessage->setAuthorName(mb_substr($name, 0, 80));
            $birthdayMessage->setMessage(mb_substr($message, 0, 500));

            $this->em->persist($birthdayMessage);
            $this->em->flush();

            $this->addFlash('voeu_success', $lang === 'fr' ? 'Merci pour votre message !' : 'Thank you for your message!');
        } else {
            $this->addFlash('voeu_error', $lang === 'fr' ? 'Merci de renseigner votre nom et un message.' : 'Please fill in your name and a message.');
        }

        return $this->redirectToRoute('landing', ['lang' => $lang, '_fragment' => 'voeux']);
    }

    #[Route('/messages', name: 'messages_list', methods: ['GET'])]
    public function list(Request $request, BirthdayMessageRepository $messages): Response
    {
        $lang = $request->query->get('lang') === 'en' ? 'en' : 'fr';
        $t = Dictionary::forLang($lang);

        return $this->render('messages.html.twig', [
            'lang' => $lang,
            't' => $t,
            'count' => $messages->countAll(),
            'messages' => $messages->findLatest(500),
        ]);
    }
}
