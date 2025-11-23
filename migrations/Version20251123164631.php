<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123164631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Elimina el campo salt de la tabla Usuario (ya no es necesario tras migración de noe2)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Usuario DROP salt');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Usuario ADD salt VARCHAR(255) NOT NULL');
    }
}
