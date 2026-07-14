<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CadreController extends AbstractController
{
    public function __construct(private readonly string $cadresSaveDir) {}

    /**
     * This photo-pledge tool only ever existed for the (now finished) "Sang Donné,
     * Vies Sauvées" blood drive, so it's archived rather than reworked — see /jyserai
     * for the legacy-URL redirect kept for anyone with an old link.
     */
    #[Route('/archive/jyserai', name: 'cadre', methods: ['GET'])]
    public function index(): Response
    {
        $images = [];
        if (is_dir($this->cadresSaveDir)) {
            $files = glob($this->cadresSaveDir . '/*.jpg') ?: [];
            usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
            $images = array_map('basename', array_slice($files, 0, 16));
        }

        return $this->render('archive/cadre.html.twig', [
            'participantImages' => $images,
        ]);
    }

    #[Route('/jyserai', name: 'cadre_legacy', methods: ['GET'])]
    public function legacyIndex(): RedirectResponse
    {
        return $this->redirectToRoute('cadre');
    }

    #[Route('/archive/jyserai/save', name: 'cadre_save', methods: ['POST'])]
    public function save(Request $request): JsonResponse
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $dataUrl = (string) ($payload['image'] ?? '');

            $prefix = 'data:image/jpeg;base64,';
            if (!str_starts_with($dataUrl, $prefix)) {
                return $this->json(['ok' => false], Response::HTTP_BAD_REQUEST);
            }

            $binary = base64_decode(substr($dataUrl, strlen($prefix)), strict: true);
            if ($binary === false || strlen($binary) < 1000 || strlen($binary) > 5_000_000) {
                return $this->json(['ok' => false], Response::HTTP_BAD_REQUEST);
            }

            if (!is_dir($this->cadresSaveDir)) {
                mkdir($this->cadresSaveDir, 0755, true);
            }

            $filename = 'jyserai_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg';
            file_put_contents($this->cadresSaveDir . '/' . $filename, $binary);

            return $this->json(['ok' => true]);
        } catch (\Throwable) {
            return $this->json(['ok' => false], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
