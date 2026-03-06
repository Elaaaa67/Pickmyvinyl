<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260228122404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE preorder (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(50) DEFAULT \'pending\' NOT NULL, vinyl_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_D9B775973FFFF645 (vinyl_id), INDEX IDX_D9B77597A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE preorder ADD CONSTRAINT FK_D9B775973FFFF645 FOREIGN KEY (vinyl_id) REFERENCES vinyl (id)');
        $this->addSql('ALTER TABLE preorder ADD CONSTRAINT FK_D9B77597A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE preorder DROP FOREIGN KEY FK_D9B775973FFFF645');
        $this->addSql('ALTER TABLE preorder DROP FOREIGN KEY FK_D9B77597A76ED395');
        $this->addSql('DROP TABLE preorder');
    }
}
