<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260217225248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A937A669A14 FOREIGN KEY (traiteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quote_request ADD status VARCHAR(30) NOT NULL, ADD created_at DATETIME NOT NULL, ADD client_id INT NOT NULL, ADD traiteur_id INT NOT NULL, ADD menu_id INT NOT NULL');
        $this->addSql('ALTER TABLE quote_request ADD CONSTRAINT FK_D478271B19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quote_request ADD CONSTRAINT FK_D478271B7A669A14 FOREIGN KEY (traiteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quote_request ADD CONSTRAINT FK_D478271BCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('CREATE INDEX IDX_D478271B19EB6921 ON quote_request (client_id)');
        $this->addSql('CREATE INDEX IDX_D478271B7A669A14 ON quote_request (traiteur_id)');
        $this->addSql('CREATE INDEX IDX_D478271BCCD7E912 ON quote_request (menu_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A937A669A14');
        $this->addSql('ALTER TABLE quote_request DROP FOREIGN KEY FK_D478271B19EB6921');
        $this->addSql('ALTER TABLE quote_request DROP FOREIGN KEY FK_D478271B7A669A14');
        $this->addSql('ALTER TABLE quote_request DROP FOREIGN KEY FK_D478271BCCD7E912');
        $this->addSql('DROP INDEX IDX_D478271B19EB6921 ON quote_request');
        $this->addSql('DROP INDEX IDX_D478271B7A669A14 ON quote_request');
        $this->addSql('DROP INDEX IDX_D478271BCCD7E912 ON quote_request');
        $this->addSql('ALTER TABLE quote_request DROP status, DROP created_at, DROP client_id, DROP traiteur_id, DROP menu_id');
    }
}
