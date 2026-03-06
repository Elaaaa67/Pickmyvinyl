<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222124714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, client_id INT DEFAULT NULL, vinyl_id INT DEFAULT NULL, store_id INT DEFAULT NULL, INDEX IDX_42C8495519EB6921 (client_id), INDEX IDX_42C849553FFFF645 (vinyl_id), INDEX IDX_42C84955B092A811 (store_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, vinyl_id INT DEFAULT NULL, store_id INT DEFAULT NULL, INDEX IDX_4B3656603FFFF645 (vinyl_id), INDEX IDX_4B365660B092A811 (store_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE store (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, owner_id INT NOT NULL, UNIQUE INDEX UNIQ_FF5758777E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vinyl (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, artist VARCHAR(255) NOT NULL, discogs_id VARCHAR(255) DEFAULT NULL, cover_image VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849553FFFF645 FOREIGN KEY (vinyl_id) REFERENCES vinyl (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955B092A811 FOREIGN KEY (store_id) REFERENCES store (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656603FFFF645 FOREIGN KEY (vinyl_id) REFERENCES vinyl (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660B092A811 FOREIGN KEY (store_id) REFERENCES store (id)');
        $this->addSql('ALTER TABLE store ADD CONSTRAINT FK_FF5758777E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519EB6921');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849553FFFF645');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955B092A811');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656603FFFF645');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660B092A811');
        $this->addSql('ALTER TABLE store DROP FOREIGN KEY FK_FF5758777E3C61F9');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE store');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE vinyl');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
