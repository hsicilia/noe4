<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123185843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convierte campos invasora, cites y peligroso a NOT NULL en tabla Especie';
    }

    public function up(Schema $schema): void
    {
        // Primero, establecer valores por defecto para los registros con NULL
        $this->addSql('UPDATE Especie SET invasora = 0 WHERE invasora IS NULL');
        $this->addSql('UPDATE Especie SET cites = 0 WHERE cites IS NULL');
        $this->addSql('UPDATE Especie SET peligroso = 0 WHERE peligroso IS NULL');

        // Ahora convertir las columnas a NOT NULL
        $this->addSql('ALTER TABLE Especie CHANGE invasora invasora TINYINT(1) NOT NULL, CHANGE cites cites SMALLINT NOT NULL, CHANGE peligroso peligroso TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Especie CHANGE invasora invasora TINYINT(1) DEFAULT NULL, CHANGE cites cites SMALLINT DEFAULT NULL, CHANGE peligroso peligroso TINYINT(1) DEFAULT NULL');
    }
}
