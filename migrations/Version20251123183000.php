<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migración de datos de campos invasora, cites y peligroso desde Ejemplar a Especie
 */
final class Version20251123183000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migra datos de invasora, cites y peligroso desde Ejemplar a Especie calculando el valor más común';
    }

    public function up(Schema $schema): void
    {
        // Migrar datos de invasora, cites y peligroso desde Ejemplar a Especie
        // Para cada especie, calculamos el valor más común entre sus ejemplares

        $especies = $this->connection->fetchAllAssociative('SELECT id FROM Especie');

        foreach ($especies as $especie) {
            $especieId = $especie['id'];

            // Contar valores de invasora
            $invasoraStats = $this->connection->fetchAllAssociative(
                'SELECT invasora, COUNT(*) as count FROM Ejemplar WHERE especie_id = ? GROUP BY invasora ORDER BY count DESC',
                [$especieId]
            );

            // Contar valores de peligroso
            $peligrosoStats = $this->connection->fetchAllAssociative(
                'SELECT peligroso, COUNT(*) as count FROM Ejemplar WHERE especie_id = ? GROUP BY peligroso ORDER BY count DESC',
                [$especieId]
            );

            // Contar valores de cites
            $citesStats = $this->connection->fetchAllAssociative(
                'SELECT cites, COUNT(*) as count FROM Ejemplar WHERE especie_id = ? GROUP BY cites ORDER BY count DESC',
                [$especieId]
            );

            // Obtener el valor más común para cada campo
            $invasoraValue = !empty($invasoraStats) ? $invasoraStats[0]['invasora'] : null;
            $peligrosoValue = !empty($peligrosoStats) ? $peligrosoStats[0]['peligroso'] : null;
            $citesValue = !empty($citesStats) ? $citesStats[0]['cites'] : null;

            // Actualizar la especie con los valores más comunes
            $this->addSql(
                'UPDATE Especie SET invasora = ?, peligroso = ?, cites = ? WHERE id = ?',
                [$invasoraValue, $peligrosoValue, $citesValue, $especieId]
            );
        }
    }

    public function down(Schema $schema): void
    {
        // No es posible revertir esta migración de datos de forma automática
        $this->addSql('UPDATE Especie SET invasora = NULL, peligroso = NULL, cites = NULL');
    }
}
