<?php

namespace App\Controller;

use App\Entity\BaobabReservation;
use App\Repository\BaobabReservationRepository;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class BaobabController extends AbstractController
{
    private const DEPARTURE_CITIES = [
        'Abomey-Calavi (Arconville, à côté du camp militaire)',
        'Abomey-Calavi (Portail secondaire du campus (UAC))',
        'Porto-Novo (En face de la piscine municipale, carrefour du Cinquantenaire)',
    ];
    private const TIME_SLOTS = ['08h00', '11h00'];
    public const MAX_TICKETS_SETTING_KEY = 'baobab_max_tickets';

    /** Departure points only served by a single time slot (e.g. Arconville only runs at 8h). */
    private const RESTRICTED_TIME_SLOTS = [
        'Abomey-Calavi (Arconville, à côté du camp militaire)' => '08h00',
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SettingRepository $settings,
    ) {
    }

    /**
     * The Baobab Express shuttle only ever ran donors to the (now finished) "Sang Donné,
     * Vies Sauvées" blood drive, so this whole flow is archived rather than reworked —
     * see /baobab for the legacy-URL redirect kept for anyone with an old link.
     */
    #[Route('/archive/baobab', name: 'baobab', methods: ['GET'])]
    public function index(BaobabReservationRepository $reservations): Response
    {
        $maxTickets = $this->settings->getInt(self::MAX_TICKETS_SETTING_KEY, 0);
        $ticketsUsed = $reservations->countAll();
        $isFull = $maxTickets > 0 && $ticketsUsed >= $maxTickets;

        return $this->render('archive/baobab.html.twig', [
            'maxTickets' => $maxTickets,
            'ticketsUsed' => $ticketsUsed,
            'ticketsRemaining' => $maxTickets > 0 ? max(0, $maxTickets - $ticketsUsed) : null,
            'isFull' => $isFull,
            'restrictedTimeSlots' => self::RESTRICTED_TIME_SLOTS,
        ]);
    }

    #[Route('/baobab', name: 'baobab_legacy', methods: ['GET'])]
    public function legacyIndex(): RedirectResponse
    {
        return $this->redirectToRoute('baobab');
    }

    #[Route('/archive/baobab/reservation', name: 'baobab_reservation_create', methods: ['POST'])]
    public function reserve(Request $request, BaobabReservationRepository $reservations): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('baobab_reservation', (string) $request->request->get('_token'))) {
            $this->addFlash('baobab_error', 'Votre session a expiré, merci de réessayer.');

            return $this->redirectToRoute('baobab', ['_fragment' => 'reservation']);
        }

        $maxTickets = $this->settings->getInt(self::MAX_TICKETS_SETTING_KEY, 0);
        if ($maxTickets > 0 && $reservations->countAll() >= $maxTickets) {
            $this->addFlash('baobab_error', 'Désolé, toutes les places ont déjà été réservées.');

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
        $restrictedSlot = self::RESTRICTED_TIME_SLOTS[$departureCity] ?? null;
        if ($restrictedSlot !== null && $timeSlot !== $restrictedSlot) {
            $errors[] = "Ce point de départ n'est desservi qu'au créneau de {$restrictedSlot}.";
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

    #[Route('/archive/baobab/ticket/{id}', name: 'baobab_ticket', methods: ['GET'])]
    public function ticket(int $id, BaobabReservationRepository $reservations): Response
    {
        $reservation = $reservations->find($id);
        if (!$reservation) {
            throw new NotFoundHttpException('Réservation introuvable.');
        }

        return $this->render('archive/baobab_ticket.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}
