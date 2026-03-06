<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304113612 extends AbstractMigration
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
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY `FK_42C849553FFFF645`');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY `FK_42C84955B092A811`');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY `FK_42C84955A76ED395`');
        $this->addSql('DROP INDEX IDX_42C84955A76ED395 ON reservation');
        $this->addSql('ALTER TABLE reservation ADD client_id INT DEFAULT NULL, DROP quantity, DROP expires_at, DROP user_id, CHANGE status status VARCHAR(100) NOT NULL, CHANGE vinyl_id vinyl_id INT DEFAULT NULL, CHANGE store_id store_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849553FFFF645 FOREIGN KEY (vinyl_id) REFERENCES vinyl (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955B092A811 FOREIGN KEY (store_id) REFERENCES store (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_42C8495519EB6921 ON reservation (client_id)');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY `FK_4B3656603FFFF645`');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY `FK_4B365660B092A811`');
        $this->addSql('ALTER TABLE stock CHANGE vinyl_id vinyl_id INT DEFAULT NULL, CHANGE store_id store_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656603FFFF645 FOREIGN KEY (vinyl_id) REFERENCES vinyl (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660B092A811 FOREIGN KEY (store_id) REFERENCES store (id)');
        $this->addSql('ALTER TABLE store DROP FOREIGN KEY `FK_FF5758777E3C61F9`');
        $this->addSql('ALTER TABLE store ADD description VARCHAR(255) NOT NULL, ADD image_url VARCHAR(255) NOT NULL, DROP phone, CHANGE address address VARCHAR(255) NOT NULL, CHANGE owner_id owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE store ADD CONSTRAINT FK_FF5758777E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user CHANGE full_name full_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649e7927c74 TO UNIQ_IDENTIFIER_EMAIL');
        $this->addSql('ALTER TABLE vinyl DROP style, DROP description, CHANGE artist artist VARCHAR(255) NOT NULL, CHANGE discogs_id discogs_id VARCHAR(255) DEFAULT NULL, CHANGE cover_url cover_image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE preorder DROP FOREIGN KEY FK_D9B775973FFFF645');
        $this->addSql('ALTER TABLE preorder DROP FOREIGN KEY FK_D9B77597A76ED395');
        $this->addSql('DROP TABLE preorder');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849553FFFF645');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955B092A811');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519EB6921');
        $this->addSql('DROP INDEX IDX_42C8495519EB6921 ON reservation');
        $this->addSql('ALTER TABLE reservation ADD quantity INT NOT NULL, ADD expires_at DATETIME DEFAULT NULL, ADD user_id INT NOT NULL, DROP client_id, CHANGE status status VARCHAR(20) NOT NULL, CHANGE vinyl_id vinyl_id INT NOT NULL, CHANGE store_id store_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT `FK_42C849553FFFF645` FOREIGN KEY (vinyl_id) REFERENCES vinyl (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT `FK_42C84955B092A811` FOREIGN KEY (store_id) REFERENCES store (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT `FK_42C84955A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_42C84955A76ED395 ON reservation (user_id)');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656603FFFF645');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660B092A811');
        $this->addSql('ALTER TABLE stock CHANGE vinyl_id vinyl_id INT NOT NULL, CHANGE store_id store_id INT NOT NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT `FK_4B3656603FFFF645` FOREIGN KEY (vinyl_id) REFERENCES vinyl (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT `FK_4B365660B092A811` FOREIGN KEY (store_id) REFERENCES store (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE store DROP FOREIGN KEY FK_FF5758777E3C61F9');
        $this->addSql('ALTER TABLE store ADD phone VARCHAR(50) DEFAULT NULL, DROP description, DROP image_url, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE owner_id owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE store ADD CONSTRAINT `FK_FF5758777E3C61F9` FOREIGN KEY (owner_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user CHANGE full_name full_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_identifier_email TO UNIQ_8D93D649E7927C74');
        $this->addSql('ALTER TABLE vinyl ADD style VARCHAR(100) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, CHANGE artist artist VARCHAR(255) DEFAULT NULL, CHANGE discogs_id discogs_id INT DEFAULT NULL, CHANGE cover_image cover_url VARCHAR(255) DEFAULT NULL');
    }
}
