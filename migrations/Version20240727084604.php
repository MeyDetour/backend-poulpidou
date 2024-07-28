<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240727084604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT fk_9065174419eb6921');
        $this->addSql('DROP INDEX idx_9065174419eb6921');
        $this->addSql('ALTER TABLE invoice DROP client_id');
        $this->addSql('ALTER TABLE "user" ADD first_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD last_name TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD phone TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD siret TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD adresse TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE invoice ADD client_id INT NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT fk_9065174419eb6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_9065174419eb6921 ON invoice (client_id)');
        $this->addSql('ALTER TABLE "user" DROP first_name');
        $this->addSql('ALTER TABLE "user" DROP last_name');
        $this->addSql('ALTER TABLE "user" DROP phone');
        $this->addSql('ALTER TABLE "user" DROP siret');
        $this->addSql('ALTER TABLE "user" DROP adresse');
    }
}
