<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240724182158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE project_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE project (id INT NOT NULL, owner_id INT NOT NULL, name TEXT NOT NULL, figma_link TEXT DEFAULT NULL, github_link TEXT DEFAULT NULL, state TEXT NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, total_price NUMERIC(15, 2) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE7E3C61F9 ON project (owner_id)');
        $this->addSql('COMMENT ON COLUMN project.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_user DROP CONSTRAINT fk_5c0f152b19eb6921');
        $this->addSql('ALTER TABLE client_user DROP CONSTRAINT fk_5c0f152ba76ed395');
        $this->addSql('DROP TABLE client_user');
        $this->addSql('ALTER TABLE client ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404557E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C74404557E3C61F9 ON client (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE project_id_seq CASCADE');
        $this->addSql('CREATE TABLE client_user (client_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(client_id, user_id))');
        $this->addSql('CREATE INDEX idx_5c0f152ba76ed395 ON client_user (user_id)');
        $this->addSql('CREATE INDEX idx_5c0f152b19eb6921 ON client_user (client_id)');
        $this->addSql('ALTER TABLE client_user ADD CONSTRAINT fk_5c0f152b19eb6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_user ADD CONSTRAINT fk_5c0f152ba76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE7E3C61F9');
        $this->addSql('DROP TABLE project');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C74404557E3C61F9');
        $this->addSql('DROP INDEX IDX_C74404557E3C61F9');
        $this->addSql('ALTER TABLE client DROP owner_id');
    }
}
