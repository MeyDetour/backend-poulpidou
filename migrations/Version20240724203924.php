<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240724203924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD client_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD current_projet_of_this_client_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEEBBD440F FOREIGN KEY (current_projet_of_this_client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE19EB6921 ON project (client_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EEEBBD440F ON project (current_projet_of_this_client_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE19EB6921');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EEEBBD440F');
        $this->addSql('DROP INDEX IDX_2FB3D0EE19EB6921');
        $this->addSql('DROP INDEX IDX_2FB3D0EEEBBD440F');
        $this->addSql('ALTER TABLE project DROP client_id');
        $this->addSql('ALTER TABLE project DROP current_projet_of_this_client_id');
    }
}
