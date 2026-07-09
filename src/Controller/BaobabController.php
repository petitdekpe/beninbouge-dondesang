<?php

namespace App\Controller;

use App\Entity\BaobabReservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $passengers = (int) $request->request->get('passengers', 1);
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
        if ($passengers < 1 || $passengers > 5) {
            $errors[] = 'Le nombre de passagers doit être compris entre 1 et 5.';
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
        $reservation->setPassengers($passengers);

        $this->em->persist($reservation);
        $this->em->flush();

        $this->addFlash('baobab_success', 'Votre réservation est confirmée ! Vous recevrez une confirmation par WhatsApp sous 24h.');

        return $this->redirectToRoute('baobab', ['_fragment' => 'reservation']);
    }
}
