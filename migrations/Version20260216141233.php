<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260216141233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, prix_par_personne DOUBLE PRECISION NOT NULL, nombre_min_personnes INT NOT NULL, nombre_max_personnes INT NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, traiteur_id INT NOT NULL, INDEX IDX_7D053A937A669A14 (traiteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quote_request (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A937A669A14 FOREIGN KEY (traiteur_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A937A669A14');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE quote_request');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE user');
    }
}
