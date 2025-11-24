<?php

namespace App\Repository;

use App\Entity\Captura;
use App\Entity\Especie;
use App\Entity\Ejemplar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ejemplar>
 */
class EjemplarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ejemplar::class);
    }

    /**
     * Encuentra ejemplares por otros identificadores (microchip, anilla, etc.)
     */
    public function encontrarOtroId(?string $otroId)
    {
        if ($otroId !== null && $otroId !== '') {
            return $this->createQueryBuilder('e')
                ->where('e.idMicrochip LIKE :id OR e.idAnilla LIKE :id OR e.idOtro LIKE :id OR e.idOtro2 LIKE :id')
                ->setParameter('id', '%' . $otroId . '%')
                ->getQuery();
        }

        return $this->createQueryBuilder('e')
            ->where('1 = 0')
            ->getQuery();
    }

    /**
     * Número de capturas de un ejemplar
     */
    public function numCapturas(int $id): int
    {
        return (int) $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(c.id)')
            ->from(Captura::class, 'c')
            ->where('c.ejemplar = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Primera captura de un ejemplar
     */
    public function primeraCaptura(int $id): ?object
    {
        return $this->getEntityManager()
            ->getRepository(Captura::class)
            ->findOneBy([
                'ejemplar' => $id,
            ], [
                'fechaCaptura' => 'ASC',
                'horaCaptura' => 'ASC',
            ]);
    }

    /**
     * Última captura de un ejemplar
     */
    public function ultimaCaptura(int $id): ?object
    {
        return $this->getEntityManager()
            ->getRepository(Captura::class)
            ->findOneBy([
                'ejemplar' => $id,
            ], [
                'fechaCaptura' => 'DESC',
                'horaCaptura' => 'DESC',
            ]);
    }

    public function informeEjemplaresCompleto(int $volumen = 0): array
    {
        return $this->informeEjemplares('completo', $volumen);
    }

    public function informeEjemplaresInvasores(int $volumen = 0): array
    {
        return $this->informeEjemplares('invasores', $volumen);
    }

    public function informeEjemplaresCites(int $volumen = 0): array
    {
        return $this->informeEjemplares('cites', $volumen);
    }

    public function informeEjemplaresEspeciales(int $volumen = 0): array
    {
        return $this->informeEjemplares('especial', $volumen);
    }

    public function buscarMapa(
        Ejemplar $ejemplar,
        ?\DateTimeInterface $fechaInicial,
        ?\DateTimeInterface $fechaFinal,
        ?\DateTimeInterface $fechaBajaInicial,
        ?\DateTimeInterface $fechaBajaFinal,
        ?float $latitud,
        ?float $longitud,
        ?float $distancia,
        string $tipoEjemplar = 'alta',
        bool $ejecutarQuery = true
    ) {
        $porDistancia = $distancia !== null && $distancia > 0;

        if ($porDistancia && $latitud !== null && $longitud !== null) {
            $caja = $this->getBoundaries($latitud, $longitud, $distancia);
            $qb = $this->createQueryBuilder('e')
                ->addSelect('6371 * ACOS(
                    SIN(RADIANS(e.geoLat)) * SIN(RADIANS(:latitud))
                    + COS(RADIANS(e.geoLong - :longitud))
                    * COS(RADIANS(e.geoLat))
                    * COS(RADIANS(:latitud))
                ) AS HIDDEN distancia')
                ->where('e.geoLat BETWEEN :minLat AND :maxLat')
                ->andWhere('e.geoLong BETWEEN :minLng AND :maxLng')
                ->setParameter('latitud', $latitud)
                ->setParameter('longitud', $longitud)
                ->setParameter('minLat', $caja['min_lat'])
                ->setParameter('maxLat', $caja['max_lat'])
                ->setParameter('minLng', $caja['min_lng'])
                ->setParameter('maxLng', $caja['max_lng']);
        } else {
            $qb = $this->createQueryBuilder('e');
        }

        if ($ejemplar->getEspecie() instanceof Especie) {
            $qb->andWhere('e.especie = :especie')
                ->setParameter('especie', $ejemplar->getEspecie());
        }

        if ($ejemplar->getSexo() !== null && $ejemplar->getSexo() !== 0) {
            $qb->andWhere('e.sexo = :sexo')
                ->setParameter('sexo', $ejemplar->getSexo());
        }

        if ($ejemplar->getRecinto() !== null && $ejemplar->getRecinto() !== '') {
            $qb->andWhere('e.recinto LIKE :recinto')
                ->setParameter('recinto', '%' . $ejemplar->getRecinto() . '%');
        }

        if ($fechaInicial instanceof \DateTimeInterface) {
            $qb->andWhere('e.fechaRegistro >= :fechaInicial')
                ->setParameter('fechaInicial', $fechaInicial->format('Y-m-d'));
        }

        if ($fechaFinal instanceof \DateTimeInterface) {
            $qb->andWhere('e.fechaRegistro <= :fechaFinal')
                ->setParameter('fechaFinal', $fechaFinal->format('Y-m-d'));
        }

        if ($ejemplar->getLugar() !== null && $ejemplar->getLugar() !== '') {
            $qb->andWhere('e.lugar LIKE :lugar')
                ->setParameter('lugar', '%' . $ejemplar->getLugar() . '%');
        }

        if ($ejemplar->getOrigen() !== null && $ejemplar->getOrigen() !== 0) {
            $qb->andWhere('e.origen = :origen')
                ->setParameter('origen', $ejemplar->getOrigen());
        }

        if ($ejemplar->getDocumentacion() !== null && $ejemplar->getDocumentacion() !== 0) {
            $qb->andWhere('e.documentacion = :documentacion')
                ->setParameter('documentacion', $ejemplar->getDocumentacion());
        }

        if ($ejemplar->getProgenitor1() !== null && $ejemplar->getProgenitor1() !== '') {
            $qb->andWhere('(e.progenitor1 LIKE :progenitor) OR (e.progenitor2 LIKE :progenitor)')
                ->setParameter('progenitor', '%' . $ejemplar->getProgenitor1() . '%');
        }

        if ($ejemplar->getDepositoNombre() !== null && $ejemplar->getDepositoNombre() !== '') {
            $qb->andWhere('e.depositoNombre LIKE :depositoNombre')
                ->setParameter('depositoNombre', '%' . $ejemplar->getDepositoNombre() . '%');
        }

        if ($ejemplar->getDepositoDNI() !== null && $ejemplar->getDepositoDNI() !== '') {
            $qb->andWhere('e.depositoDNI LIKE :depositoDNI')
                ->setParameter('depositoDNI', '%' . $ejemplar->getDepositoDNI() . '%');
        }

        // Para invasora, peligroso y cites: consultar desde Especie (no desde Ejemplar)
        // Accedemos a propiedades dinámicas porque estos campos ya no están en Ejemplar
        $invasora = $ejemplar->invasora ?? null;
        $peligroso = $ejemplar->peligroso ?? null;
        $cites = $ejemplar->cites ?? null;

        $necesitaJoinEspecie = $invasora !== null || $peligroso !== null || $cites !== null;

        if ($necesitaJoinEspecie) {
            $qb->join('e.especie', 'esp_filtro');

            if ($invasora !== null) {
                $qb->andWhere('esp_filtro.invasora = :invasora')
                    ->setParameter('invasora', $invasora);
            }

            if ($peligroso !== null) {
                $qb->andWhere('esp_filtro.peligroso = :peligroso')
                    ->setParameter('peligroso', $peligroso);
            }

            if ($cites !== null) {
                $qb->andWhere('esp_filtro.cites = :cites')
                    ->setParameter('cites', $cites);
            }
        }

        // Filtrar por tipo de ejemplar
        if ($tipoEjemplar === 'alta') {
            $qb->andWhere('e.fechaBaja IS NULL');
        } elseif ($tipoEjemplar === 'baja') {
            $qb->andWhere('e.fechaBaja IS NOT NULL');
        }

        // Si es 'todos', no se aplica ningún filtro adicional

        // Los campos de fecha de baja solo se aplican si el tipo es 'baja'
        if ($tipoEjemplar === 'baja') {
            if ($fechaBajaInicial instanceof \DateTimeInterface) {
                $qb->andWhere('e.fechaBaja >= :fechaBajaInicial')
                    ->setParameter('fechaBajaInicial', $fechaBajaInicial->format('Y-m-d'));
            }

            if ($fechaBajaFinal instanceof \DateTimeInterface) {
                $qb->andWhere('e.fechaBaja <= :fechaBajaFinal')
                    ->setParameter('fechaBajaFinal', $fechaBajaFinal->format('Y-m-d'));
            }

            if ($ejemplar->getCausaBaja() !== null) {
                $qb->andWhere('e.causaBaja = :causaBaja')
                    ->setParameter('causaBaja', $ejemplar->getCausaBaja());
            }
        }

        $qb->orderBy('e.id', 'ASC');

        $query = $qb->getQuery();

        return $ejecutarQuery ? $query->getResult() : $query;
    }

    private function informeEjemplares(string $tipo, int $volumen = 0): array
    {
        $EJEMPLARES_POR_VOLUMEN = 500;

        $queryBuilder = $this->createQueryBuilder('e')
            ->where('e.fechaBaja IS NULL')
            ->andWhere('e.causaBaja IS NULL');

        switch ($tipo) {
            case 'invasores':
                $queryBuilder->join('e.especie', 'esp_informe')
                    ->andWhere('esp_informe.invasora = true');
                break;
            case 'cites':
                $queryBuilder->join('e.especie', 'esp_informe')
                    ->andWhere('esp_informe.cites > 0');
                break;
        }

        $queryBuilder->orderBy('e.origen', 'ASC')
            ->addOrderBy('e.id', 'ASC');

        if ($volumen > 0) {
            $queryBuilder->setFirstResult(($volumen - 1) * $EJEMPLARES_POR_VOLUMEN)
                ->setMaxResults($EJEMPLARES_POR_VOLUMEN);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    private function getBoundaries(float $lat, float $lng, float $distance, float $earthRadius = 6371): array
    {
        $return = [];

        // Los ángulos para cada dirección
        $cardinalCoords = [
            'north' => 0,
            'south' => 180,
            'east' => 90,
            'west' => 270,
        ];

        $rLat = deg2rad($lat);
        $rLng = deg2rad($lng);
        $rAngDist = $distance / $earthRadius;

        foreach ($cardinalCoords as $name => $angle) {
            $rAngle = deg2rad($angle);
            $rLatB = asin(sin($rLat) * cos($rAngDist) + cos($rLat) * sin($rAngDist) * cos($rAngle));
            $rLonB = $rLng + atan2(sin($rAngle) * sin($rAngDist) * cos($rLat), cos($rAngDist) - sin($rLat) * sin($rLatB));

            $return[$name] = [
                'lat' => rad2deg($rLatB),
                'lng' => rad2deg($rLonB),
            ];
        }

        return [
            'min_lat' => $return['south']['lat'],
            'max_lat' => $return['north']['lat'],
            'min_lng' => $return['west']['lng'],
            'max_lng' => $return['east']['lng'],
        ];
    }
}
