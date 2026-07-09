<?php

namespace App\Controller;

use App\Repository\BaobabReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminBaobabReservationController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/admin/baobab-reservations', name: 'admin_baobab_reservations', methods: ['GET'])]
    public function index(BaobabReservationRepository $reservations): Response
    {
        return $this->render('admin/baobab_reservations.html.twig', [
            'reservations' => $reservations->findAllForAdmin(),
            'totalPassengers' => $reservations->sumPassengers(),
        ]);
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
