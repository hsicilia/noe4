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
    description: 'Genera un archivo CSV con el listado de especies y sus campos invasora, peligroso y cites',
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

        // Obtener valores únicos de CITES desde Ejemplar
        $citesValues = $this->entityManager->createQueryBuilder()
            ->select('DISTINCT e.cites')
            ->from('App\Entity\Ejemplar', 'e')
            ->where('e.cites IS NOT NULL')
            ->getQuery()
            ->getSingleColumnResult();

        // Preparar archivo CSV
        $filename = 'analisis_especies_' . date('Y-m-d_His') . '.csv';
        $filepath = __DIR__ . '/../../var/' . $filename;

        // Crear directorio var si no existe
        if (!is_dir(__DIR__ . '/../../var')) {
            if (!mkdir($concurrentDirectory = __DIR__.'/../../var', 0755, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
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

        $especiesActualizadas = 0;

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

            // Determinar valor más común para invasora
            $maxInvasora = max($invasoraTrue, $invasoraFalse, $invasoraNull);
            if ($maxInvasora === $invasoraTrue) {
                $especie->setInvasora(true);
            } elseif ($maxInvasora === $invasoraFalse) {
                $especie->setInvasora(false);
            } else {
                $especie->setInvasora(null);
            }

            // Determinar valor más común para peligroso
            $maxPeligroso = max($peligrosoTrue, $peligrosoFalse, $peligrosoNull);
            if ($maxPeligroso === $peligrosoTrue) {
                $especie->setPeligroso(true);
            } elseif ($maxPeligroso === $peligrosoFalse) {
                $especie->setPeligroso(false);
            } else {
                $especie->setPeligroso(null);
            }

            // Determinar valor más común para CITES
            arsort($citesCounts);
            $citesMaxValue = array_key_first($citesCounts);
            $especie->setCites($citesMaxValue);

            $especiesActualizadas++;

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

        // Guardar todos los cambios en la base de datos
        $this->entityManager->flush();

        $io->success([
            sprintf('Análisis completado: %d especies procesadas.', count($especies)),
            sprintf('Especies actualizadas con valores más comunes: %d', $especiesActualizadas),
            sprintf('Archivo CSV generado: %s', $filepath)
        ]);

        return Command::SUCCESS;
    }
}
