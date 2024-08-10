<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240810130209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT fk_906517447e3c61f9');
        $this->addSql('DROP INDEX idx_906517447e3c61f9');
        $this->addSql('ALTER TABLE invoice DROP owner_id');
        $this->addSql('ALTER TABLE project ADD other_user_can_edit_invoices BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD can_other_user_see_client_profile BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE invoice ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT fk_906517447e3c61f9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_906517447e3c61f9 ON invoice (owner_id)');
        $this->addSql('ALTER TABLE project DROP other_user_can_edit_invoices');
        $this->addSql('ALTER TABLE project DROP can_other_user_see_client_profile');
    }
}
