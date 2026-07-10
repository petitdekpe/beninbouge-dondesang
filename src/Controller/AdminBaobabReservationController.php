<?php

namespace App\Controller;

use App\Entity\BaobabReservation;
use App\Repository\BaobabReservationRepository;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
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
    public function index(Request $request, BaobabReservationRepository $reservations): Response
    {
        $city = trim((string) $request->query->get('city', '')) ?: null;
        $timeSlot = trim((string) $request->query->get('time_slot', '')) ?: null;
        $search = trim((string) $request->query->get('q', '')) ?: null;

        $filtered = $reservations->findFiltered($city, $timeSlot, $search);

        return $this->render('admin/baobab_reservations.html.twig', [
            'reservations' => $filtered,
            'totalPassengers' => array_sum(array_map(static fn ($r) => $r->getPassengers(), $filtered)),
            'totalCount' => $reservations->countAll(),
            'maxTickets' => $this->settings->getInt(BaobabController::MAX_TICKETS_SETTING_KEY, 0),
            'statsByCity' => $reservations->countByDepartureCityAndTimeSlot(),
            'cities' => $reservations->findDistinctDepartureCities(),
            'timeSlots' => $reservations->findDistinctTimeSlots(),
            'filterCity' => $city,
            'filterTimeSlot' => $timeSlot,
            'filterSearch' => $search,
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
    public function export(Request $request, BaobabReservationRepository $reservations): StreamedResponse
    {
        [$city, $timeSlot, $search] = $this->readFilters($request);
        $grouped = $this->groupForExport($reservations->findFilteredForExport($city, $timeSlot, $search));

        $response = new StreamedResponse(function () use ($grouped): void {
            $handle = fopen('php://output', 'w+');
            fwrite($handle, "\xEF\xBB\xBF");
            $header = ['N° ticket', 'Nom complet', 'Téléphone', 'Ville de départ', 'Créneau', 'Passagers', 'Date de réservation'];

            foreach ($grouped as $city => $cityData) {
                fputcsv($handle, ["DESTINATION : {$city}", '', '', '', '', "{$cityData['count']} réservation(s), {$cityData['passengers']} passager(s)", ''], ';');
                fputcsv($handle, $header, ';');

                foreach ($cityData['slots'] as $slot => $slotData) {
                    fputcsv($handle, ["Créneau {$slot}", '', '', '', '', "{$slotData['passengers']} passager(s)", ''], ';');
                    foreach ($slotData['reservations'] as $r) {
                        fputcsv($handle, [
                            'N°' . sprintf('%03d', $r->getId()),
                            $r->getFullName(),
                            $r->getPhone(),
                            $r->getDepartureCity(),
                            $r->getTimeSlot(),
                            $r->getPassengers(),
                            $r->getCreatedAt()->format('d/m/Y H:i'),
                        ], ';');
                    }
                }
                fputcsv($handle, [], ';');
            }

            fclose($handle);
        });

        $filename = 'reservations-baobab-' . (new \DateTimeImmutable())->format('Y-m-d_His') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    #[Route('/admin/baobab-reservations/export.pdf', name: 'admin_baobab_reservations_export_pdf', methods: ['GET'])]
    public function exportPdf(Request $request, BaobabReservationRepository $reservations): Response
    {
        [$city, $timeSlot, $search] = $this->readFilters($request);
        $rows = $reservations->findFilteredForExport($city, $timeSlot, $search);
        $grouped = $this->groupForExport($rows);

        $html = $this->renderView('admin/baobab_reservations_pdf.html.twig', [
            'grouped' => $grouped,
            'totalCount' => count($rows),
            'totalPassengers' => array_sum(array_map(static fn (BaobabReservation $r) => $r->getPassengers(), $rows)),
            'filterCity' => $city,
            'filterTimeSlot' => $timeSlot,
            'filterSearch' => $search,
            'generatedAt' => new \DateTimeImmutable(),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'reservations-baobab-' . (new \DateTimeImmutable())->format('Y-m-d_His') . '.pdf';

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string}
     */
    private function readFilters(Request $request): array
    {
        return [
            trim((string) $request->query->get('city', '')) ?: null,
            trim((string) $request->query->get('time_slot', '')) ?: null,
            trim((string) $request->query->get('q', '')) ?: null,
        ];
    }

    /**
     * Groups reservations by destination, then by time slot, with running
     * totals at each level — used by both the CSV and PDF exports.
     *
     * @param BaobabReservation[] $rows
     * @return array<string, array{count: int, passengers: int, slots: array<string, array{passengers: int, reservations: BaobabReservation[]}>}>
     */
    private function groupForExport(array $rows): array
    {
        $grouped = [];
        foreach ($rows as $r) {
            $city = $r->getDepartureCity();
            $slot = $r->getTimeSlot();

            $grouped[$city]['count'] = ($grouped[$city]['count'] ?? 0) + 1;
            $grouped[$city]['passengers'] = ($grouped[$city]['passengers'] ?? 0) + $r->getPassengers();
            $grouped[$city]['slots'][$slot]['passengers'] = ($grouped[$city]['slots'][$slot]['passengers'] ?? 0) + $r->getPassengers();
            $grouped[$city]['slots'][$slot]['reservations'][] = $r;
        }

        return $grouped;
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
