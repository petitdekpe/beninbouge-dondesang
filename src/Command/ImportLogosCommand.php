<?php

namespace App\Command;

use App\Entity\Partner;
use App\Entity\Sponsor;
use App\Repository\PartnerRepository;
use App\Repository\SponsorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Imports Sponsor/Partner rows from the logo files sitting in
 * public/assets/Sponsors, public/assets/Partenaires and
 * public/assets/Partenaires techniques — the filename (without
 * extension) becomes the company name. Logos are copied into the
 * same public/uploads/{sponsors,partners} directories the admin
 * upload forms use, so they render exactly like a manually-added entry.
 */
#[AsCommand(
    name: 'app:import-logos',
    description: 'Importe les Sponsors/Partenaires/Partenaires techniques à partir des logos dans public/assets',
)]
final class ImportLogosCommand extends Command
{
    private const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp', 'svg'];

    /** @var array<string, array{entity: string, category: ?string}> */
    private const SOURCE_FOLDERS = [
        'Sponsors' => ['entity' => 'sponsor', 'category' => null],
        'Partenaires' => ['entity' => 'partner', 'category' => 'partenaire'],
        'Partenaires techniques' => ['entity' => 'partner', 'category' => 'technique'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PartnerRepository $partners,
        private readonly SponsorRepository $sponsors,
        private readonly string $partnersUploadDir,
        private readonly string $sponsorsUploadDir,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Prévisualiser sans écrire en base ni copier de fichiers')
            ->addOption('assets-dir', null, InputOption::VALUE_REQUIRED, 'Dossier source contenant les sous-dossiers Sponsors/Partenaires/Partenaires techniques', '%kernel.project_dir%/public/assets');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $assetsDir = rtrim(str_replace('%kernel.project_dir%', $this->projectDir, (string) $input->getOption('assets-dir')), '/');

        $io->title('Import des logos Sponsors / Partenaires');
        if ($dryRun) {
            $io->note('Mode dry-run : aucune écriture ne sera effectuée.');
        }

        $slugger = new AsciiSlugger();
        $rows = [];
        $created = 0;
        $skipped = 0;

        foreach (self::SOURCE_FOLDERS as $folder => $config) {
            $sourceDir = $assetsDir . '/' . $folder;
            if (!is_dir($sourceDir)) {
                $io->warning("Dossier introuvable, ignoré : {$sourceDir}");
                continue;
            }

            $files = glob($sourceDir . '/*') ?: [];
            sort($files);

            foreach ($files as $filePath) {
                if (!is_file($filePath)) {
                    continue;
                }

                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                    continue;
                }

                $name = $this->deriveName($filePath);
                if ($name === '') {
                    continue;
                }

                $category = $config['category'];
                $exists = $config['entity'] === 'sponsor'
                    ? $this->sponsors->findOneBy(['name' => $name]) !== null
                    : $this->partners->findOneBy(['name' => $name, 'category' => $category]) !== null;

                if ($exists) {
                    $rows[] = [$folder, $name, 'Ignoré (existe déjà)'];
                    $skipped++;
                    continue;
                }

                if (!$dryRun) {
                    $uploadDir = $config['entity'] === 'sponsor' ? $this->sponsorsUploadDir : $this->partnersUploadDir;
                    $safeName = $slugger->slug(pathinfo($filePath, PATHINFO_FILENAME))->lower();
                    $filename = $safeName . '-' . uniqid() . '.' . $extension;

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    if (!copy($filePath, $uploadDir . '/' . $filename)) {
                        $rows[] = [$folder, $name, 'Échec de copie du fichier'];
                        continue;
                    }

                    if ($config['entity'] === 'sponsor') {
                        $sponsor = new Sponsor();
                        $sponsor->setName($name);
                        $sponsor->setLogoFilename($filename);
                        $this->em->persist($sponsor);
                    } else {
                        $partner = new Partner();
                        $partner->setName($name);
                        $partner->setLogoFilename($filename);
                        $partner->setCategory($category);
                        $this->em->persist($partner);
                    }
                }

                $rows[] = [$folder, $name, $dryRun ? 'À créer' : 'Créé'];
                $created++;
            }
        }

        if (!$dryRun) {
            $this->em->flush();
        }

        $io->table(['Dossier', 'Nom', 'Statut'], $rows);
        $io->success(sprintf(
            '%d entrée(s) %s, %d ignorée(s) (déjà existantes).',
            $created,
            $dryRun ? 'à créer' : 'créée(s)',
            $skipped,
        ));

        return Command::SUCCESS;
    }

    private function deriveName(string $filePath): string
    {
        $filename = pathinfo($filePath, PATHINFO_FILENAME);

        // Handles accidental double extensions such as "SO FRUITY.jpg.png".
        foreach (self::ALLOWED_EXTENSIONS as $ext) {
            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === $ext) {
                $filename = pathinfo($filename, PATHINFO_FILENAME);
                break;
            }
        }

        return trim($filename);
    }
}
