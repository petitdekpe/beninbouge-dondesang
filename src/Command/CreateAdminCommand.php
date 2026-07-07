<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Définit le mot de passe du compte admin et met à jour .env.local',
)]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création / mise à jour du compte admin');

        $password = $io->askHidden('Nouveau mot de passe');
        if (!$password) {
            $io->error('Le mot de passe ne peut pas être vide.');
            return Command::FAILURE;
        }

        $confirm = $io->askHidden('Confirmez le mot de passe');
        if ($password !== $confirm) {
            $io->error('Les mots de passe ne correspondent pas.');
            return Command::FAILURE;
        }

        if (strlen($password) < 8) {
            $io->error('Le mot de passe doit contenir au moins 8 caractères.');
            return Command::FAILURE;
        }

        $user = new InMemoryUser('admin', null, ['ROLE_ADMIN']);
        $hash = $this->hasher->hashPassword($user, $password);

        $envFile = $this->projectDir . '/.env.local';
        $line    = "ADMIN_PASSWORD_HASH='" . $hash . "'";

        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            if (str_contains($content, 'ADMIN_PASSWORD_HASH=')) {
                $content = preg_replace('/^ADMIN_PASSWORD_HASH=.*/m', $line, $content);
            } else {
                $content = rtrim($content) . "\n" . $line . "\n";
            }
        } else {
            $content = $line . "\n";
        }

        file_put_contents($envFile, $content);

        $io->success('Mot de passe admin mis à jour dans .env.local');
        $io->note('Relancez le cache si vous êtes en prod : php bin/console cache:clear --env=prod');

        return Command::SUCCESS;
    }
}
