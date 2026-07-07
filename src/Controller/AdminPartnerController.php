<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Repository\PartnerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[IsGranted('ROLE_ADMIN')]
final class AdminPartnerController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    private const ALLOWED_MIME_TYPES = ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'];
    private const ALLOWED_CATEGORIES = ['technique', 'partenaire'];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string $partnersUploadDir,
    ) {
    }

    #[Route('/admin/partenaires', name: 'admin_partners', methods: ['GET'])]
    public function index(PartnerRepository $partners): Response
    {
        return $this->render('admin/partners.html.twig', [
            'partners' => $partners->findAllForAdmin(),
        ]);
    }

    #[Route('/admin/partenaires/create', name: 'admin_partners_create', methods: ['POST'])]
    public function create(Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_partner_create', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_partners');
        }

        $name     = trim((string) $request->request->get('name', ''));
        $role     = trim((string) $request->request->get('role', ''));
        $website  = trim((string) $request->request->get('website', ''));
        $category = (string) $request->request->get('category', 'technique');
        $logo     = $request->files->get('logo');

        if ($name === '' || !$logo || !in_array($logo->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            $this->addFlash('admin_error', 'Le nom et un logo valide (PNG, JPG, WebP ou SVG) sont obligatoires.');
            return $this->redirectToRoute('admin_partners');
        }

        if (!in_array($category, self::ALLOWED_CATEGORIES, true)) {
            $category = 'technique';
        }

        $slugger  = new AsciiSlugger();
        $safeName = $slugger->slug(pathinfo($logo->getClientOriginalName(), PATHINFO_FILENAME))->lower();
        $filename = $safeName . '-' . uniqid() . '.' . $logo->guessExtension();

        try {
            $logo->move($this->partnersUploadDir, $filename);
        } catch (FileException) {
            $this->addFlash('admin_error', 'Le téléchargement du logo a échoué.');
            return $this->redirectToRoute('admin_partners');
        }

        $partner = new Partner();
        $partner->setName(mb_substr($name, 0, 120));
        $partner->setLogoFilename($filename);
        $partner->setCategory($category);
        if ($role !== '') {
            $partner->setRole(mb_substr($role, 0, 255));
        }
        if ($website !== '') {
            if (!preg_match('#^https?://#i', $website)) {
                $website = 'https://' . $website;
            }
            $partner->setWebsiteUrl(mb_substr($website, 0, 255));
        }

        $this->em->persist($partner);
        $this->em->flush();

        $this->addFlash('admin_success', 'Partenaire ajouté.');
        return $this->redirectToRoute('admin_partners');
    }

    #[Route('/admin/partenaires/{id}/delete', name: 'admin_partners_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, PartnerRepository $partners): RedirectResponse
    {
        $partner = $partners->find($id);
        if ($partner && $this->isCsrfTokenValid('admin_partner_' . $id, (string) $request->request->get('_token'))) {
            $path = $this->partnersUploadDir . '/' . $partner->getLogoFilename();
            if (is_file($path)) {
                @unlink($path);
            }
            $this->em->remove($partner);
            $this->em->flush();
            $this->addFlash('admin_success', 'Partenaire supprimé.');
        }

        return $this->redirectToRoute('admin_partners');
    }
}
