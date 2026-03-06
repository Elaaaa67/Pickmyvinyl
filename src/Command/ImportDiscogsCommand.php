<?php

namespace App\Command;

use App\Entity\Vinyl;
use App\Entity\Store;
use App\Entity\Stock;
use App\Entity\User;
use Calliostro\Discogs\DiscogsClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:import-discogs', description: 'Importe des vinyles depuis Discogs pour remplir la BDD (exemples).')]
class ImportDiscogsCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private ?DiscogsClient $client = null, private ?UserPasswordHasherInterface $hasher = null)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('count', null, InputOption::VALUE_OPTIONAL, 'Nombre de vinyles à importer', 20);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = (int) $input->getOption('count');

        if (!$this->client) {
            $output->writeln('<comment>DiscogsClient non disponible, création d\'exemples statiques.</comment>');
            for ($i = 1; $i <= $count; $i++) {
                $v = new Vinyl();
                $v->setTitle('Exemple Titre ' . $i);
                $v->setArtist('Artiste ' . $i);
                $this->em->persist($v);
            }
            $this->em->flush();

            $output->writeln('<info>Import statique terminé.</info>');
            return Command::SUCCESS;
        }

        try {
            $results = $this->client->search(type: 'release', format: 'vinyl', perPage: $count);
        } catch (\Throwable $e) {
            $output->writeln('<error>Erreur API Discogs : ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $items = $results['results'] ?? [];

        foreach ($items as $item) {
            $v = new Vinyl();
            $v->setTitle($item['title'] ?? 'Titre inconnu');
            $v->setArtist($item['title'] ? explode(' - ', $item['title'], 2)[0] ?? 'Artiste inconnu' : 'Artiste inconnu');
            $v->setDiscogsId((string)($item['id'] ?? ''));
            $v->setCoverImage($item['cover_image'] ?? $item['thumb'] ?? null);
            $this->em->persist($v);
        }

        // Créer quelques boutiques et stocks d'exemple
        $user = new User();
        $user->setEmail('owner@example.com');
        if ($this->hasher) {
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
        } else {
            $user->setPassword('password');
        }
        $user->setRoles(['ROLE_SHOP']);
        $this->em->persist($user);

        $store = new Store();
        $store->setName('Boutique Ex.' );
        $store->setAddress('1 rue Exemple');
        $store->setOwner($user);
        $this->em->persist($store);

        $this->em->flush();

        // Lier quelques stocks
        $vinyls = $this->em->getRepository(Vinyl::class)->findAll();
        $i = 0;
        foreach ($vinyls as $v) {
            if ($i++ > 10) break;
            $stock = new Stock();
            $stock->setVinyl($v);
            $stock->setStore($store);
            $stock->setQuantity(random_int(1, 10));
            $this->em->persist($stock);
        }

        $this->em->flush();

        $output->writeln('<info>Import Discogs terminé — ' . count($vinyls) . ' vinyles ajoutés.</info>');

        return Command::SUCCESS;
    }
}

