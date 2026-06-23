<?php

namespace App\Controller;

use App\Repository\BirthdayMessageRepository;
use App\Repository\SponsorRepository;
use App\Translation\Dictionary;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PageController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    private const CHIP_AMOUNTS = [2000, 5000, 10000, 25000, 50000];

    private const PAYMENT_METHODS = [
        ['id' => 'mtn', 'name' => 'MTN Mobile Money', 'abbr' => 'MTN', 'color' => '#F8C61C', 'ink' => '#14160F'],
        ['id' => 'moov', 'name' => 'Moov Money', 'abbr' => 'Moov', 'color' => '#0F61A8', 'ink' => '#fff'],
        ['id' => 'celtiis', 'name' => 'Celtiis Cash', 'abbr' => 'Cs', 'color' => '#0F8A40', 'ink' => '#fff'],
    ];

    public function __construct(
        private readonly BirthdayMessageRepository $messages,
        private readonly SponsorRepository $sponsors,
    ) {
    }

    #[Route('/', name: 'landing', methods: ['GET'])]
    public function landing(Request $request): Response
    {
        $lang = $request->query->get('lang') === 'en' ? 'en' : 'fr';
        $t = Dictionary::forLang($lang);

        $cardMethod = [
            'id' => 'card',
            'name' => $lang === 'fr' ? 'Carte bancaire (Visa / Mastercard)' : 'Bank card (Visa / Mastercard)',
            'abbr' => '💳', 'color' => '#295D3C', 'ink' => '#fff',
        ];

        return $this->render('landing.html.twig', [
            'lang' => $lang,
            't' => $t,
            'chipAmounts' => self::CHIP_AMOUNTS,
            'methods' => array_merge(self::PAYMENT_METHODS, [$cardMethod]),
            'fedapayPublicKey' => $_ENV['FEDAPAY_PUBLIC_KEY'] ?? 'pk_sandbox_XXXXXXXXXXXXXXXXXXXXXXXXXXXX',
            'birthdayMessages' => $this->messages->findLatest(12),
            'sponsors' => $this->sponsors->findAllOrdered(),
        ]);
    }

    #[Route('/merci', name: 'merci', methods: ['GET'])]
    public function merci(Request $request): Response
    {
        $lang = $request->query->get('lang') === 'en' ? 'en' : 'fr';
        $t = Dictionary::forLang($lang);

        $amount = (int) preg_replace('/\D/', '', (string) $request->query->get('amount', '5000'));
        $name = trim((string) $request->query->get('name', ''));
        $reference = 'BB-2026-' . random_int(100000, 999999);

        return $this->render('merci.html.twig', [
            'lang' => $lang,
            't' => $t,
            'amount' => $amount > 0 ? $amount : 5000,
            'donorName' => $name !== '' ? $name : ($lang === 'fr' ? 'Donateur anonyme' : 'Anonymous donor'),
            'reference' => $reference,
        ]);
    }
}
