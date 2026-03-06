<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Store;
use App\Entity\Vinyl;
use App\Entity\Stock;
use App\Entity\Reservation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // --- 1. ADMIN ---
        $admin = new User();
        $admin->setEmail('admin@pickmyvinyl.com')
            ->setFullName('Admin Principal')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // --- 2. ENSEIGNES (USERS + STORES) ---
        for ($i = 1; $i <= 5; $i++) {
            $owner = new User();
            $owner->setEmail("pro$i@test.com")
                ->setFullName($faker->name)
                ->setRoles(['ROLE_STORE'])
                ->setPassword($this->hasher->hashPassword($owner, 'password'));
            $manager->persist($owner);

            $store = new Store();
            // On s'assure que name n'est JAMAIS null ici
            $storeName = $faker->company;
            $store->setName($storeName)
                ->setAddress($faker->address)
                ->setDescription($faker->catchPhrase)
                ->setImageUrl("https://picsum.photos/seed/store$i/600/400")
                ->setOwner($owner);

            $manager->persist($store);
            $this->addReference('store_' . $i, $store);
        }

        // --- 3. CLIENTS ---
        for ($k = 1; $k <= 10; $k++) {
            $client = new User();
            $client->setEmail("client$k@test.com")
                ->setFullName($faker->name)
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->hasher->hashPassword($client, 'password'));

            $manager->persist($client);
            $this->addReference('client_' . $k, $client);
        }

        // --- 4. VINYLES ---
        for ($j = 1; $j <= 20; $j++) {
            $vinyl = new Vinyl();
            $vinyl->setTitle($faker->sentence(3))
                ->setArtist($faker->name)
                ->setCoverImage("https://picsum.photos/seed/vinyl$j/300/300")
                ->setDiscogsId((string)$faker->randomNumber(8));

            $manager->persist($vinyl);
            $this->addReference('vinyl_' . $j, $vinyl);

            // Ajout de stock pour ce vinyle
            $stock = new Stock();
            $stock->setVinyl($vinyl)
                ->setStore($this->getReference('store_' . rand(1, 5), Store::class))
                ->setQuantity(rand(1, 10));
            $manager->persist($stock);
        }

        // --- 5. RÉSERVATIONS ---
        for ($r = 1; $r <= 15; $r++) {
            $res = new Reservation();
            $res->setCreatedAt(new \DateTimeImmutable())
                ->setStatus($faker->randomElement(['pending', 'confirmed', 'collected']))
                ->setClient($this->getReference('client_' . rand(1, 10), User::class))
                ->setStore($this->getReference('store_' . rand(1, 5), Store::class))
                ->setVinyl($this->getReference('vinyl_' . rand(1, 20), Vinyl::class));

            $manager->persist($res);
        }

        $manager->flush();
    }
}
