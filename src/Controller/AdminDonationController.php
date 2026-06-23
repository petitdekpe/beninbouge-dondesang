<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Repository\DonationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminDonationController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    private const METHOD_LABELS = [
        'tiers' => 'Via tiers',
        'cash' => 'Espèces',
        'fedapay_link' => 'Lien direct FedaPay',
        // legacy values kept for donations recorded before this list changed
        'mtn' => 'MTN Mobile Money',
        'moov' => 'Moov Money',
        'celtiis' => 'Celtiis Cash',
        'card' => 'Carte bancaire',
        'other' => 'Autre',
    ];

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/admin/dons', name: 'admin_donations', methods: ['GET'])]
    public function index(DonationRepository $donations): Response
    {
        return $this->render('admin/donations.html.twig', [
            'donations' => $donations->findAllForAdmin(),
            'total' => $donations->sumApprovedAmount(),
            'method_labels' => self::METHOD_LABELS,
        ]);
    }

    #[Route('/admin/dons/create', name: 'admin_donations_create', methods: ['POST'])]
    public function create(Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_donation_create', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_donations');
        }

        $amount = (int) $request->request->get('amount', 0);
        if ($amount > 0) {
            $donation = new Donation();
            $donation->setAmount($amount);
            $donation->setDonorName(self::clip($request->request->get('donor_name'), 120));
            $donation->setAnonymous((bool) $request->request->get('anonymous'));
            $donation->setMethod(self::clip($request->request->get('method'), 20));
            $donation->setStatus((string) $request->request->get('status', 'approved'));
            if ($donation->getStatus() === 'approved') {
                $donation->setConfirmedAt(new \DateTimeImmutable());
            }

            $this->em->persist($donation);
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_donations');
    }

    #[Route('/admin/dons/{id}/toggle', name: 'admin_donations_toggle', methods: ['POST'])]
    public function toggle(int $id, Request $request, DonationRepository $donations): RedirectResponse
    {
        $donation = $donations->find($id);
        if ($donation && $this->isCsrfTokenValid('admin_donation_' . $id, (string) $request->request->get('_token'))) {
            $donation->setVisible(!$donation->isVisible());
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_donations');
    }

    private static function clip(mixed $value, int $maxLength): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return mb_substr(trim($value), 0, $maxLength);
    }
}
