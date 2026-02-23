<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218095222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A937A669A14 FOREIGN KEY (traiteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quote_request ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE quote_request ADD CONSTRAINT FK_D478271B19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quote_request ADD CONSTRAINT FK_D478271B7A669A14 FOREIGN KEY (traiteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quote_request ADD CONSTRAINT FK_D478271BCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE review ADD note INT NOT NULL, ADD commentaire LONGTEXT DEFAULT NULL, ADD date_creation DATETIME NOT NULL, ADD client_id INT NOT NULL, ADD traiteur_id INT NOT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C619EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C67A669A14 FOREIGN KEY (traiteur_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_794381C619EB6921 ON review (client_id)');
        $this->addSql('CREATE INDEX IDX_794381C67A669A14 ON review (traiteur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A937A669A14');
        $this->addSql('ALTER TABLE quote_request DROP FOREIGN KEY FK_D478271B19EB6921');
        $this->addSql('ALTER TABLE quote_request DROP FOREIGN KEY FK_D478271B7A669A14');
        $this->addSql('ALTER TABLE quote_request DROP FOREIGN KEY FK_D478271BCCD7E912');
        $this->addSql('ALTER TABLE quote_request DROP updated_at');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C619EB6921');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C67A669A14');
        $this->addSql('DROP INDEX IDX_794381C619EB6921 ON review');
        $this->addSql('DROP INDEX IDX_794381C67A669A14 ON review');
        $this->addSql('ALTER TABLE review DROP note, DROP commentaire, DROP date_creation, DROP client_id, DROP traiteur_id');
    }
}
