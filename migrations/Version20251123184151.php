<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123184151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Elimina campos invasora, cites y peligroso de tabla Ejemplar (migrados a Especie)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Ejemplar DROP invasora, DROP cites, DROP peligroso');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Ejemplar ADD invasora TINYINT(1) DEFAULT NULL, ADD cites SMALLINT NOT NULL, ADD peligroso TINYINT(1) DEFAULT NULL');
    }
}
