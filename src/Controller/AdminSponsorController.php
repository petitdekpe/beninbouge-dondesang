<?php

namespace App\Controller;

use App\Entity\Sponsor;
use App\Repository\SponsorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[IsGranted('ROLE_ADMIN')]
final class AdminSponsorController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    private const ALLOWED_MIME_TYPES = ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string $sponsorsUploadDir,
    ) {
    }

    #[Route('/admin/sponsors', name: 'admin_sponsors', methods: ['GET'])]
    public function index(SponsorRepository $sponsors): Response
    {
        return $this->render('admin/sponsors.html.twig', [
            'sponsors' => $sponsors->findAllOrdered(),
        ]);
    }

    #[Route('/admin/sponsors/create', name: 'admin_sponsors_create', methods: ['POST'])]
    public function create(Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_sponsor_create', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_sponsors');
        }

        $name = trim((string) $request->request->get('name', ''));
        $website = trim((string) $request->request->get('website', ''));
        $logo = $request->files->get('logo');

        if ($name === '' || !$logo || !in_array($logo->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            $this->addFlash('admin_error', 'Le nom et un logo (PNG, JPG, WebP ou SVG) sont obligatoires.');

            return $this->redirectToRoute('admin_sponsors');
        }

        $slugger = new AsciiSlugger();
        $safeName = $slugger->slug(pathinfo($logo->getClientOriginalName(), PATHINFO_FILENAME))->lower();
        $filename = $safeName . '-' . uniqid() . '.' . $logo->guessExtension();

        try {
            $logo->move($this->sponsorsUploadDir, $filename);
        } catch (FileException) {
            $this->addFlash('admin_error', "Le téléchargement du logo a échoué.");

            return $this->redirectToRoute('admin_sponsors');
        }

        $sponsor = new Sponsor();
        $sponsor->setName(mb_substr($name, 0, 120));
        $sponsor->setLogoFilename($filename);
        if ($website !== '') {
            if (!preg_match('#^https?://#i', $website)) {
                $website = 'https://' . $website;
            }
            $sponsor->setWebsiteUrl(mb_substr($website, 0, 255));
        }

        $this->em->persist($sponsor);
        $this->em->flush();

        $this->addFlash('admin_success', 'Sponsor ajouté.');

        return $this->redirectToRoute('admin_sponsors');
    }

    #[Route('/admin/sponsors/{id}/delete', name: 'admin_sponsors_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, SponsorRepository $sponsors): RedirectResponse
    {
        $sponsor = $sponsors->find($id);
        if ($sponsor && $this->isCsrfTokenValid('admin_sponsor_' . $id, (string) $request->request->get('_token'))) {
            $path = $this->sponsorsUploadDir . '/' . $sponsor->getLogoFilename();
            if (is_file($path)) {
                @unlink($path);
            }
            $this->em->remove($sponsor);
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_sponsors');
    }
}
