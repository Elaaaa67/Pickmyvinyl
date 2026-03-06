<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-user', description: 'Crée un utilisateur (optionnellement admin) pour les tests')]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email de l\'utilisateur', 'admin@example.test')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Mot de passe', 'admin')
            ->addOption('roles', null, InputOption::VALUE_REQUIRED, 'Roles séparés par des virgules', 'ROLE_ADMIN')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Forcer la mise à jour si l\'email existe');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = (string) $input->getOption('email');
        $password = (string) $input->getOption('password');
        $rolesOption = (string) $input->getOption('roles');
        $roles = array_filter(array_map('trim', explode(',', $rolesOption)));
        if (empty($roles)) {
            $roles = ['ROLE_USER'];
        }

        $userRepo = $this->em->getRepository(User::class);
        $existing = $userRepo->findOneBy(['email' => $email]);

        if ($existing && !$input->getOption('force')) {
            $io->warning("Un utilisateur avec l'email $email existe déjà. Utilisez --force pour mettre à jour le mot de passe.");
            return Command::FAILURE;
        }

        if (!$existing) {
            $user = new User();
            $user->setEmail($email);
        } else {
            $user = $existing;
            $io->text("Mise à jour de l'utilisateur existant {$email}");
        }

        $user->setRoles($roles);
        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);
        $user->setIsVerified(true);

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Utilisateur %s créé/mis à jour avec succès (roles: %s)', $email, implode(',', $roles)));

        return Command::SUCCESS;
    }
}

