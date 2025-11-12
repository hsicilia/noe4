<?php

namespace App\Command;

use App\Entity\Especie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:analisis-especies',
    description: 'Genera un archivo CSV con análisis de invasora, peligroso y cites por especie',
)]
class AnalisisEspeciesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Análisis de campos invasora, peligroso y cites por especie');

        // Obtener todas las especies con sus ejemplares
        $especies = $this->entityManager->getRepository(Especie::class)->findAll();

        if (empty($especies)) {
            $io->warning('No se encontraron especies en la base de datos.');
            return Command::FAILURE;
        }

        // Primero, obtener todos los valores únicos de CITES
        $citesValuesQuery = $this->entityManager->createQuery(
            'SELECT DISTINCT e.cites FROM App\Entity\Ejemplar e ORDER BY e.cites'
        );
        $citesValues = array_column($citesValuesQuery->getResult(), 'cites');

        // Preparar archivo CSV
        $filename = 'analisis_especies_' . date('Y-m-d_His') . '.csv';
        $filepath = __DIR__ . '/../../var/' . $filename;

        // Crear directorio var si no existe
        if (!is_dir(__DIR__ . '/../../var')) {
            mkdir(__DIR__ . '/../../var', 0755, true);
        }

        $file = fopen($filepath, 'w');

        // Escribir BOM para que Excel detecte UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Cabeceras
        $headers = [
            'ID',
            'Nombre científico',
            'Nombre común',
            'Total ejemplares',
            'Invasora=true',
            'Invasora=false',
            'Invasora=null',
            'Peligroso=true',
            'Peligroso=false',
            'Peligroso=null'
        ];

        // Añadir columnas para cada valor de CITES
        foreach ($citesValues as $citesValue) {
            $headers[] = "CITES=$citesValue";
        }

        fputcsv($file, $headers, ';');

        $io->progressStart(count($especies));

        // Procesar cada especie
        foreach ($especies as $especie) {
            $ejemplares = $especie->getEjemplares();
            $totalEjemplares = count($ejemplares);

            // Inicializar contadores
            $invasoraTrue = 0;
            $invasoraFalse = 0;
            $invasoraNull = 0;
            $peligrosoTrue = 0;
            $peligrosoFalse = 0;
            $peligrosoNull = 0;
            $citesCounts = array_fill_keys($citesValues, 0);

            // Contar valores
            foreach ($ejemplares as $ejemplar) {
                // Invasora
                $invasora = $ejemplar->getInvasora();
                if ($invasora === true) {
                    $invasoraTrue++;
                } elseif ($invasora === false) {
                    $invasoraFalse++;
                } else {
                    $invasoraNull++;
                }

                // Peligroso
                $peligroso = $ejemplar->getPeligroso();
                if ($peligroso === true) {
                    $peligrosoTrue++;
                } elseif ($peligroso === false) {
                    $peligrosoFalse++;
                } else {
                    $peligrosoNull++;
                }

                // CITES
                $cites = $ejemplar->getCites();
                if (isset($citesCounts[$cites])) {
                    $citesCounts[$cites]++;
                }
            }

            // Preparar fila
            $row = [
                $especie->getId(),
                $especie->getNombre(),
                $especie->getComun(),
                $totalEjemplares,
                $invasoraTrue,
                $invasoraFalse,
                $invasoraNull,
                $peligrosoTrue,
                $peligrosoFalse,
                $peligrosoNull
            ];

            // Añadir conteos de CITES
            foreach ($citesValues as $citesValue) {
                $row[] = $citesCounts[$citesValue];
            }

            fputcsv($file, $row, ';');
            $io->progressAdvance();
        }

        fclose($file);
        $io->progressFinish();

        $io->success([
            sprintf('Análisis completado: %d especies procesadas.', count($especies)),
            sprintf('Archivo generado: %s', $filepath)
        ]);

        return Command::SUCCESS;
    }
}
