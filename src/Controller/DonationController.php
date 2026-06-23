<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Repository\DonationRepository;
use App\Translation\Dictionary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Records a donation as "pending" the moment the donor opens the FedaPay
 * checkout widget, so the donor's name/email/phone/method are captured
 * locally even though FedaPay's webhook (the source of truth for payment
 * status) doesn't carry all of them back. The webhook later matches this
 * row by id (embedded in the FedaPay transaction description) and flips it
 * to "approved"/"declined".
 */
final class DonationController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/api/donations', name: 'donations_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];

        $amount = (int) ($data['amount'] ?? 0);
        if ($amount <= 0) {
            return new JsonResponse(['error' => 'invalid amount'], 400);
        }

        $donation = new Donation();
        $donation->setAmount($amount);
        $donation->setDonorName(self::clip($data['name'] ?? null, 120));
        $donation->setEmail(self::clip($data['email'] ?? null, 180));
        $donation->setPhone(self::clip($data['phone'] ?? null, 40));
        $donation->setAnonymous((bool) ($data['anonymous'] ?? false));
        $donation->setMethod(self::clip($data['method'] ?? null, 20));
        $donation->setStatus('pending');

        $this->em->persist($donation);
        $this->em->flush();

        return new JsonResponse(['id' => $donation->getId()], 201);
    }

    #[Route('/dons', name: 'donations_track', methods: ['GET'])]
    public function track(Request $request, DonationRepository $donations): Response
    {
        $lang = $request->query->get('lang') === 'en' ? 'en' : 'fr';
        $t = Dictionary::forLang($lang);

        return $this->render('dons.html.twig', [
            'lang' => $lang,
            't' => $t,
            'total' => $donations->sumApprovedAmount(),
            'count' => $donations->countApproved(),
            'donations' => $donations->findApproved(),
        ]);
    }

    private static function clip(mixed $value, int $maxLength): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return mb_substr(trim($value), 0, $maxLength);
    }
}
