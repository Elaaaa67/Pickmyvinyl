<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginTest extends WebTestCase
{
    public function testAdminCanLogin(): void
    {
        // Force l'utilisation d'une base SQLite en mémoire pour ce test (évite l'absence de driver)
        putenv('DATABASE_URL=sqlite:///:memory:');

        $client = static::createClient();

        // Création du schéma Doctrine en mémoire
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $meta = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($meta)) {
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
            $schemaTool->createSchema($meta);
        }

        // Crée un utilisateur admin dans la DB de test
        $passwordHasher = $container->get('security.password_hasher');
        $user = new \App\Entity\User();
        $user->setEmail('admin@example.test');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setIsVerified(true);
        $user->setPassword($passwordHasher->hashPassword($user, 'admin'));
        $em->persist($user);
        $em->flush();

        // Accède à la page de login et soumet le formulaire (flux réel)
        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();
        $form['email'] = 'admin@example.test';
        $form['password'] = 'admin';
        $client->submit($form);

        // Vérifie la redirection après login
        $this->assertResponseRedirects('/login-success', 302);
        $client->followRedirect();

        // Vérifie qu'on est connecté en cherchant un lien de logout (route app_logout -> /logout)
        $this->assertSelectorExists('a[href="/logout"]', 'Le lien de déconnexion doit être présent après login');
    }
}
