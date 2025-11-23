<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123182137 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade campos invasora, cites y peligroso a la tabla Especie (migración de Ejemplar a Especie)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Especie ADD invasora TINYINT(1) DEFAULT NULL, ADD cites SMALLINT DEFAULT NULL, ADD peligroso TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Especie DROP invasora, DROP cites, DROP peligroso');
    }
}
