<?php

namespace App\Controller;

use App\Entity\BaobabReservation;
use App\Repository\BaobabReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class BaobabController extends AbstractController
{
    private const DEPARTURE_CITIES = ['Abomey-Calavi', 'Porto-Novo'];
    private const TIME_SLOTS = ['08h00', '11h00'];

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/baobab', name: 'baobab', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('baobab.html.twig');
    }

    #[Route('/baobab/reservation', name: 'baobab_reservation_create', methods: ['POST'])]
    public function reserve(Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('baobab_reservation', (string) $request->request->get('_token'))) {
            $this->addFlash('baobab_error', 'Votre session a expiré, merci de réessayer.');

            return $this->redirectToRoute('baobab', ['_fragment' => 'reservation']);
        }

        $fullName = trim((string) $request->request->get('full_name', ''));
        $phone = trim((string) $request->request->get('phone', ''));
        $departureCity = trim((string) $request->request->get('departure_city', ''));
        $timeSlot = trim((string) $request->request->get('time_slot', ''));
        $consent = (bool) $request->request->get('consent', false);

        $errors = [];
        if ($fullName === '') {
            $errors[] = 'Merci de renseigner votre nom complet.';
        }
        if ($phone === '') {
            $errors[] = 'Merci de renseigner votre numéro WhatsApp.';
        }
        if (!in_array($departureCity, self::DEPARTURE_CITIES, true)) {
            $errors[] = 'Merci de sélectionner une ville de départ valide.';
        }
        if (!in_array($timeSlot, self::TIME_SLOTS, true)) {
            $errors[] = 'Merci de sélectionner un créneau de départ valide.';
        }
        if (!$consent) {
            $errors[] = 'Merci de confirmer votre engagement à respecter les horaires de départ.';
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->addFlash('baobab_error', $error);
            }

            return $this->redirectToRoute('baobab', ['_fragment' => 'reservation']);
        }

        $reservation = new BaobabReservation();
        $reservation->setFullName(mb_substr($fullName, 0, 120));
        $reservation->setPhone(mb_substr($phone, 0, 40));
        $reservation->setDepartureCity($departureCity);
        $reservation->setTimeSlot($timeSlot);

        $this->em->persist($reservation);
        $this->em->flush();

        return $this->redirectToRoute('baobab_ticket', ['id' => $reservation->getId()]);
    }

    #[Route('/baobab/ticket/{id}', name: 'baobab_ticket', methods: ['GET'])]
    public function ticket(int $id, BaobabReservationRepository $reservations): Response
    {
        $reservation = $reservations->find($id);
        if (!$reservation) {
            throw new NotFoundHttpException('Réservation introuvable.');
        }

        return $this->render('baobab_ticket.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}
