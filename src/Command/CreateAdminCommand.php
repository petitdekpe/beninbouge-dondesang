<?php

namespace App\Command;

use App\Entity\AdminUser;
use App\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée ou met à jour un compte admin en base de données',
)]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly AdminUserRepository $adminUsers,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création / mise à jour d’un compte admin');

        $username = $io->ask('Nom d’utilisateur');
        if (!$username) {
            $io->error('Le nom d’utilisateur ne peut pas être vide.');
            return Command::FAILURE;
        }

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

        $user = $this->adminUsers->findOneBy(['username' => $username]);
        $isNew = $user === null;
        if ($user === null) {
            $user = new AdminUser();
            $user->setUsername($username);
        }

        $user->setPassword($this->hasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf(
            $isNew ? 'Compte admin "%s" créé.' : 'Mot de passe du compte admin "%s" mis à jour.',
            $username,
        ));
        $io->note('Relancez le cache si vous êtes en prod : php bin/console cache:clear --env=prod');

        return Command::SUCCESS;
    }
}
