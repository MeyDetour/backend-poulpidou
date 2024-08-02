<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240731141415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE pdf_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE pdf (id INT NOT NULL, owner_id INT NOT NULL, type TEXT NOT NULL, file_name TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EF0DB8C7E3C61F9 ON pdf (owner_id)');
        $this->addSql('ALTER TABLE pdf ADD CONSTRAINT FK_EF0DB8C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE pdf_id_seq CASCADE');
        $this->addSql('ALTER TABLE pdf DROP CONSTRAINT FK_EF0DB8C7E3C61F9');
        $this->addSql('DROP TABLE pdf');
    }
}
