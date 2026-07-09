<?php

namespace App\Controller;

use App\Repository\BaobabReservationRepository;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminBaobabReservationController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SettingRepository $settings,
    ) {
    }

    #[Route('/admin/baobab-reservations', name: 'admin_baobab_reservations', methods: ['GET'])]
    public function index(BaobabReservationRepository $reservations): Response
    {
        return $this->render('admin/baobab_reservations.html.twig', [
            'reservations' => $reservations->findAllForAdmin(),
            'totalPassengers' => $reservations->sumPassengers(),
            'maxTickets' => $this->settings->getInt(BaobabController::MAX_TICKETS_SETTING_KEY, 0),
        ]);
    }

    #[Route('/admin/baobab-reservations/max-tickets', name: 'admin_baobab_reservations_max_tickets', methods: ['POST'])]
    public function updateMaxTickets(Request $request): RedirectResponse
    {
        if ($this->isCsrfTokenValid('admin_baobab_max_tickets', (string) $request->request->get('_token'))) {
            $maxTickets = max(0, (int) $request->request->get('max_tickets', 0));
            $this->settings->setValue($this->em, BaobabController::MAX_TICKETS_SETTING_KEY, (string) $maxTickets);
            $this->addFlash('admin_success', $maxTickets > 0
                ? "Limite mise à jour : {$maxTickets} ticket(s) maximum."
                : 'Limite désactivée : nombre de tickets illimité.');
        }

        return $this->redirectToRoute('admin_baobab_reservations');
    }

    #[Route('/admin/baobab-reservations/export', name: 'admin_baobab_reservations_export', methods: ['GET'])]
    public function export(BaobabReservationRepository $reservations): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($reservations): void {
            $handle = fopen('php://output', 'w+');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Nom complet', 'Téléphone', 'Ville de départ', 'Créneau', 'Passagers', 'Date de réservation'], ';');

            foreach ($reservations->findAllForAdmin() as $r) {
                fputcsv($handle, [
                    $r->getFullName(),
                    $r->getPhone(),
                    $r->getDepartureCity(),
                    $r->getTimeSlot(),
                    $r->getPassengers(),
                    $r->getCreatedAt()->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($handle);
        });

        $filename = 'reservations-baobab-' . (new \DateTimeImmutable())->format('Y-m-d_His') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    #[Route('/admin/baobab-reservations/{id}/delete', name: 'admin_baobab_reservations_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, BaobabReservationRepository $reservations): RedirectResponse
    {
        $reservation = $reservations->find($id);
        if ($reservation && $this->isCsrfTokenValid('admin_baobab_reservation_' . $id, (string) $request->request->get('_token'))) {
            $this->em->remove($reservation);
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_baobab_reservations');
    }
}
