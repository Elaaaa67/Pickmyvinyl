<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304115256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849553FFFF645 FOREIGN KEY (vinyl_id) REFERENCES vinyl (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955B092A811 FOREIGN KEY (store_id) REFERENCES store (id)');
        $this->addSql('ALTER TABLE store ADD description VARCHAR(255) NOT NULL, ADD image_url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user ADD full_name VARCHAR(255) NOT NULL, ADD name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849553FFFF645');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955B092A811');
        $this->addSql('ALTER TABLE store DROP description, DROP image_url');
        $this->addSql('ALTER TABLE user DROP full_name, DROP name');
    }
}
