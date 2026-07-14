<?php

namespace App\Controller;

use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminSettingsController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public const DONATIONS_ENABLED_SETTING_KEY = 'donations_enabled';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SettingRepository $settings,
    ) {
    }

    #[Route('/admin/reglages', name: 'admin_settings', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/settings.html.twig', [
            'donationsEnabled' => $this->settings->getBool(self::DONATIONS_ENABLED_SETTING_KEY, true),
        ]);
    }

    #[Route('/admin/reglages/dons', name: 'admin_settings_toggle_donations', methods: ['POST'])]
    public function toggleDonations(Request $request): RedirectResponse
    {
        if ($this->isCsrfTokenValid('admin_toggle_donations', (string) $request->request->get('_token'))) {
            $enabled = (bool) $request->request->get('enabled', false);
            $this->settings->setValue($this->em, self::DONATIONS_ENABLED_SETTING_KEY, $enabled ? '1' : '0');
            $this->addFlash('admin_success', $enabled
                ? 'Le bloc don et les boutons de contribution sont maintenant affichés sur le site.'
                : 'Le bloc don et les boutons de contribution sont maintenant masqués sur le site.');
        }

        return $this->redirectToRoute('admin_settings');
    }
}
